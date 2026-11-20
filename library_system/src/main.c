#include <stdio.h>
#include <string.h>
#include "freertos/FreeRTOS.h"
#include "freertos/task.h"
#include "esp_system.h"
#include "esp_wifi.h"
#include "esp_event.h"
#include "esp_log.h"
#include "nvs_flash.h"
#include "esp_netif.h"
#include "esp_http_client.h"
#include "driver/gpio.h"
#include "driver/spi_master.h"
#include "rc522.h"
#include "driver/rc522_spi.h"

static const char *TAG = "LIBRARY";

#define WIFI_SSID  "Bachelor_Gang"
#define WIFI_PASS  "banankomuna69"
#define SERVER_URL "http://192.168.0.110:8080/PROJECT/esp_receive.php?uid="

static rc522_driver_handle_t driver;
static rc522_handle_t        scanner;

static char     last_uid[20] = "";
static uint32_t last_time    = 0;
#define DEBOUNCE_MS 5000

// ── HTTP GET ──────────────────────────────────────
static void send_uid(const char *uid) {
    char url[200];
    snprintf(url, sizeof(url), "%s%s", SERVER_URL, uid);

    esp_http_client_config_t cfg = {
        .url        = url,
        .method     = HTTP_METHOD_GET,
        .timeout_ms = 5000,
    };
    esp_http_client_handle_t client = esp_http_client_init(&cfg);
    esp_err_t err = esp_http_client_perform(client);

    if (err == ESP_OK) {
        char response[64] = {0};
        esp_http_client_read(client, response, sizeof(response) - 1);
        ESP_LOGI(TAG, "HTTP %d | %s",
                 esp_http_client_get_status_code(client), response);
    } else {
        ESP_LOGE(TAG, "HTTP Error: %s", esp_err_to_name(err));
    }
    esp_http_client_cleanup(client);
}

// ── RFID Scan Event ───────────────────────────────
static void on_picc_state_changed(void *arg, esp_event_base_t base,
                                   int32_t event_id, void *data) {
    rc522_picc_state_changed_event_t *event =
        (rc522_picc_state_changed_event_t *)data;
    rc522_picc_t *picc = event->picc;

    if (picc->state != RC522_PICC_STATE_ACTIVE) return;

    // UID → HEX string
    char uid[20] = "";
    char hex[3];
    for (int i = 0; i < picc->uid.length; i++) {
        snprintf(hex, sizeof(hex), "%02X", picc->uid.value[i]);
        strcat(uid, hex);
    }

    // Debounce
    uint32_t now = xTaskGetTickCount() * portTICK_PERIOD_MS;
    if (strcmp(uid, last_uid) == 0 && (now - last_time) < DEBOUNCE_MS) {
        ESP_LOGW(TAG, "Debounce ignore: %s", uid);
        return;
    }
    strncpy(last_uid, uid, sizeof(last_uid) - 1);
    last_time = now;

    ESP_LOGI(TAG, "Scanned: %s", uid);
    send_uid(uid);
}

// ── WiFi ──────────────────────────────────────────
static void wifi_handler(void *arg, esp_event_base_t base,
                          int32_t event_id, void *data) {
    if (base == WIFI_EVENT && event_id == WIFI_EVENT_STA_START) {
        esp_wifi_connect();
    } else if (base == WIFI_EVENT && event_id == WIFI_EVENT_STA_DISCONNECTED) {
        ESP_LOGW(TAG, "Reconnecting...");
        esp_wifi_connect();
    } else if (base == IP_EVENT && event_id == IP_EVENT_STA_GOT_IP) {
        ip_event_got_ip_t *e = (ip_event_got_ip_t *)data;
        ESP_LOGI(TAG, "IP: " IPSTR, IP2STR(&e->ip_info.ip));
    }
}

static void wifi_init(void) {
    esp_netif_init();
    esp_event_loop_create_default();
    esp_netif_create_default_wifi_sta();

    wifi_init_config_t cfg = WIFI_INIT_CONFIG_DEFAULT();
    esp_wifi_init(&cfg);

    esp_event_handler_instance_register(WIFI_EVENT, ESP_EVENT_ANY_ID,
                                         wifi_handler, NULL, NULL);
    esp_event_handler_instance_register(IP_EVENT, IP_EVENT_STA_GOT_IP,
                                         wifi_handler, NULL, NULL);

    wifi_config_t wifi_cfg = {
        .sta = {
            .ssid     = WIFI_SSID,
            .password = WIFI_PASS,
        },
    };
    esp_wifi_set_mode(WIFI_MODE_STA);
    esp_wifi_set_config(WIFI_IF_STA, &wifi_cfg);
    esp_wifi_start();
}

// ── app_main ──────────────────────────────────────
void app_main(void) {
    esp_err_t ret = nvs_flash_init();
    if (ret == ESP_ERR_NVS_NO_FREE_PAGES ||
        ret == ESP_ERR_NVS_NEW_VERSION_FOUND) {
        ESP_ERROR_CHECK(nvs_flash_erase());
        ret = nvs_flash_init();
    }
    ESP_ERROR_CHECK(ret);

    ESP_LOGI(TAG, "Starting...");
    wifi_init();

    // RST pin HIGH
    gpio_set_direction(GPIO_NUM_22, GPIO_MODE_OUTPUT);
    gpio_set_level(GPIO_NUM_22, 1);

    // SPI Bus config
    spi_bus_config_t bus_cfg = {
        .mosi_io_num   = 23,
        .miso_io_num   = 19,
        .sclk_io_num   = 18,
        .quadwp_io_num = -1,
        .quadhd_io_num = -1,
    };

    // SPI Device config
    spi_device_interface_config_t dev_cfg = {
        .clock_speed_hz = 5000000,
        .mode           = 0,
        .spics_io_num   = 5,
        .queue_size     = 7,
    };

    // RC522 SPI config (v3.x)
    rc522_spi_config_t spi_cfg = {
        .host_id    = SPI3_HOST,
        .bus_config = &bus_cfg,
        .dev_config = dev_cfg,
    };

    rc522_spi_create(&spi_cfg, &driver);
    rc522_driver_install(driver);

    rc522_config_t scanner_cfg = {
        .driver = driver,
    };
    rc522_create(&scanner_cfg, &scanner);
    rc522_register_events(scanner, RC522_EVENT_PICC_STATE_CHANGED,
                          on_picc_state_changed, NULL);
    rc522_start(scanner);

    ESP_LOGI(TAG, "Ready! Scan a card...");
}