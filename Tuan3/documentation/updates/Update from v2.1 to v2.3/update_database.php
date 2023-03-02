<?php
defined('ENVIRONMENT') || define('ENVIRONMENT', 'development');
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(__DIR__);
$pathsConfig = FCPATH . 'app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;
$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require realpath($bootstrap) ?: $bootstrap;

$dbArray = new \Config\Database();
$connection = mysqli_connect($dbArray->default['hostname'], $dbArray->default['username'], $dbArray->default['password'], $dbArray->default['database']);
if (empty($connection)) {
    echo 'Database connection failed! Check your database credentials in the "app/Config/Database.php" file.';
    exit();
}
$connection->query("SET CHARACTER SET utf8");
$connection->query("SET NAMES utf8");

function runQuery($sql)
{
    global $connection;
    return mysqli_query($connection, $sql);
}

if (isset($_POST["btn_submit"])) {
    update($connection);
    $success = 'The update has been successfully completed! Please delete the "update_database.php" file.';
}

function update()
{
    updateFrom21To22();
    sleep(1);
    updateFrom22To23();
}

function updateFrom21To22()
{
    runQuery("ALTER TABLE orders ADD COLUMN `coupon_products` TEXT;");
    runQuery("ALTER TABLE earnings CHANGE `price` `sale_amount` bigint(20)");
    runQuery("ALTER TABLE earnings ADD COLUMN `vat_rate` double");
    runQuery("ALTER TABLE earnings ADD COLUMN `vat_amount` bigint(20)");
    runQuery("ALTER TABLE earnings ADD COLUMN `commission` bigint(20)");
    runQuery("ALTER TABLE earnings ADD COLUMN `coupon_discount` bigint(20)");
    runQuery("ALTER TABLE general_settings ADD COLUMN `product_image_limit` smallint(6) DEFAULT 20");
    runQuery("UPDATE general_settings SET version='2.2' WHERE id='1'");
    sleep(1);
    //add new translations
    $p = array();
    $p["error_image_limit"] = "Image upload limit exceeded!";
    $p["product_image_upload_limit"] = "Product Image Upload Limit";
    $p["commission"] = "Commission";
    addTranslations($p);
}

function updateFrom22To23()
{
    global $connection;

    runQuery("DROP TABLE ad_spaces;");
    runQuery("DROP TABLE ci_sessions;");
    runQuery("DROP TABLE fonts;");

    $tableAdSpaces = "CREATE TABLE `ad_spaces` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `lang_id` int(11) DEFAULT 1,
      `ad_space` text DEFAULT NULL,
      `ad_code_desktop` text DEFAULT NULL,
      `desktop_width` int(11) DEFAULT NULL,
      `desktop_height` int(11) DEFAULT NULL,
      `ad_code_mobile` text DEFAULT NULL,
      `mobile_width` int(11) DEFAULT NULL,
      `mobile_height` int(11) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $tableCI = "CREATE TABLE `ci_sessions` (
    `id` varchar(128) NOT null,
    `ip_address` varchar(45) NOT null,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT null,
    `data` blob NOT null,
    KEY `ci_sessions_timestamp` (`timestamp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $tableEmailQueue = "CREATE TABLE `email_queue` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `email_type` varchar(50) DEFAULT NULL,
      `email_address` varchar(255) DEFAULT NULL,
      `email_subject` varchar(255) DEFAULT NULL,
      `email_data` text DEFAULT NULL,
      `email_priority` smallint(6) DEFAULT 2,
      `template_path` varchar(255) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp()
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $tableFonts = "CREATE TABLE `fonts` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `font_name` varchar(255) DEFAULT NULL,
      `font_key` varchar(255) DEFAULT NULL,
      `font_url` varchar(2000) DEFAULT NULL,
      `font_family` varchar(500) DEFAULT NULL,
      `font_source` varchar(50) DEFAULT 'google',
      `has_local_file` tinyint(1) DEFAULT 0,
      `is_default` tinyint(1) DEFAULT 0
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    runQuery($tableAdSpaces);
    runQuery($tableCI);
    runQuery($tableEmailQueue);
    runQuery($tableFonts);
    sleep(1);

    runQuery("ALTER TABLE general_settings CHANGE custom_css_codes custom_header_codes mediumtext;");
    runQuery("ALTER TABLE general_settings CHANGE custom_javascript_codes custom_footer_codes mediumtext;");
    runQuery("ALTER TABLE general_settings CHANGE mail_library mail_service varchar(100) DEFAULT 'swift';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `mailjet_api_key` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `mailjet_secret_key` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `mailjet_email_address` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `watermark_text` varchar(255) DEFAULT 'Modesy';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `watermark_font_size` smallint(6) DEFAULT 42;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `watermark_image_large`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `watermark_image_mid`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `watermark_image_small`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `static_content_cache_system`;");
    runQuery("ALTER TABLE general_settings CHANGE product_cache_system cache_system TINYINT(1) DEFAULT 0;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `product_image_limit`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `max_file_size_image`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `max_file_size_video`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `max_file_size_audio`;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `show_customer_email_seller` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `show_customer_phone_seller` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `newsletter_image` varchar(255);");
    runQuery("ALTER TABLE general_settings DROP COLUMN `last_cron_update_long`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `mds_key`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `purchase_code`;");
    runQuery("ALTER TABLE orders ADD COLUMN `shipping` TEXT;");
    runQuery("ALTER TABLE order_products CHANGE product_quantity product_quantity INT;");
    runQuery("ALTER TABLE payment_gateways DROP COLUMN `locale`;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `product_image_limit` smallint(6) DEFAULT 20;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `max_file_size_image` bigint(20) DEFAULT 10485760;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `max_file_size_video` bigint(20) DEFAULT 31457280;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `max_file_size_audio` bigint(20) DEFAULT 10485760;");
    runQuery("ALTER TABLE users DROP COLUMN `has_active_shop`;");
    runQuery("ALTER TABLE users DROP COLUMN `shop_name`;");
    runQuery("ALTER TABLE storage_settings DROP COLUMN `aws_base_url`;");

    //update shipping
    $shippingAddresses = runQuery("SELECT * FROM order_shipping ORDER BY id;");
    if (!empty($shippingAddresses->num_rows)) {
        while ($item = mysqli_fetch_array($shippingAddresses)) {
            $array = [
                'sFirstName' => $item['shipping_first_name'],
                'sLastName' => $item['shipping_last_name'],
                'sEmail' => $item['shipping_email'],
                'sPhoneNumber' => $item['shipping_phone_number'],
                'sAddress' => $item['shipping_address'],
                'sCountry' => $item['shipping_country'],
                'sState' => $item['shipping_state'],
                'sCity' => $item['shipping_city'],
                'sZipCode' => $item['shipping_zip_code'],
                'bFirstName' => $item['billing_first_name'],
                'bLastName' => $item['billing_last_name'],
                'bEmail' => $item['billing_email'],
                'bPhoneNumber' => $item['billing_phone_number'],
                'bAddress' => $item['billing_address'],
                'bCountry' => $item['billing_country'],
                'bState' => $item['billing_state'],
                'bCity' => $item['billing_city'],
                'bZipCode' => $item['billing_zip_code']
            ];
            $serialized = serialize($array);
            $serialized = mysqli_real_escape_string($connection, $serialized);
            runQuery("Update orders SET `shipping`='" . $serialized . "' WHERE `id`=" . $item['order_id'] . " ;");
        }
    }

    $sqlFonts = "INSERT INTO `fonts` (`id`, `font_name`, `font_key`, `font_url`, `font_family`, `font_source`, `has_local_file`, `is_default`) VALUES
(1, 'Arial', 'arial', NULL, 'font-family: Arial, Helvetica, sans-serif', 'local', 0, 1),
(2, 'Arvo', 'arvo', '<link href=\"https://fonts.googleapis.com/css?family=Arvo:400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Arvo\", Helvetica, sans-serif', 'google', 0, 0),
(3, 'Averia Libre', 'averia-libre', '<link href=\"https://fonts.googleapis.com/css?family=Averia+Libre:300,400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Averia Libre\", Helvetica, sans-serif', 'google', 0, 0),
(4, 'Bitter', 'bitter', '<link href=\"https://fonts.googleapis.com/css?family=Bitter:400,400i,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Bitter\", Helvetica, sans-serif', 'google', 0, 0),
(5, 'Cabin', 'cabin', '<link href=\"https://fonts.googleapis.com/css?family=Cabin:400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Cabin\", Helvetica, sans-serif', 'google', 0, 0),
(6, 'Cherry Swash', 'cherry-swash', '<link href=\"https://fonts.googleapis.com/css?family=Cherry+Swash:400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Cherry Swash\", Helvetica, sans-serif', 'google', 0, 0),
(7, 'Encode Sans', 'encode-sans', '<link href=\"https://fonts.googleapis.com/css?family=Encode+Sans:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Encode Sans\", Helvetica, sans-serif', 'google', 0, 0),
(8, 'Helvetica', 'helvetica', NULL, 'font-family: Helvetica, sans-serif', 'local', 0, 1),
(9, 'Hind', 'hind', '<link href=\"https://fonts.googleapis.com/css?family=Hind:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Hind\", Helvetica, sans-serif', 'google', 0, 0),
(10, 'Josefin Sans', 'josefin-sans', '<link href=\"https://fonts.googleapis.com/css?family=Josefin+Sans:300,400,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Josefin Sans\", Helvetica, sans-serif', 'google', 0, 0),
(11, 'Kalam', 'kalam', '<link href=\"https://fonts.googleapis.com/css?family=Kalam:300,400,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Kalam\", Helvetica, sans-serif', 'google', 0, 0),
(12, 'Khula', 'khula', '<link href=\"https://fonts.googleapis.com/css?family=Khula:300,400,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Khula\", Helvetica, sans-serif', 'google', 0, 0),
(13, 'Lato', 'lato', '<link href=\"https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Lato\", Helvetica, sans-serif', 'google', 0, 0),
(14, 'Lora', 'lora', '<link href=\"https://fonts.googleapis.com/css?family=Lora:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Lora\", Helvetica, sans-serif', 'google', 0, 0),
(15, 'Merriweather', 'merriweather', '<link href=\"https://fonts.googleapis.com/css?family=Merriweather:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Merriweather\", Helvetica, sans-serif', 'google', 0, 0),
(16, 'Montserrat', 'montserrat', '<link href=\"https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Montserrat\", Helvetica, sans-serif', 'google', 0, 0),
(17, 'Mukta', 'mukta', '<link href=\"https://fonts.googleapis.com/css?family=Mukta:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Mukta\", Helvetica, sans-serif', 'google', 0, 0),
(18, 'Nunito', 'nunito', '<link href=\"https://fonts.googleapis.com/css?family=Nunito:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Nunito\", Helvetica, sans-serif', 'google', 0, 0),
(19, 'Open Sans', 'open-sans', '<link href=\"https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap\" rel=\"stylesheet\">', 'font-family: \"Open Sans\", Helvetica, sans-serif', 'local', 1, 0),
(20, 'Oswald', 'oswald', '<link href=\"https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Oswald\", Helvetica, sans-serif', 'google', 0, 0),
(21, 'Oxygen', 'oxygen', '<link href=\"https://fonts.googleapis.com/css?family=Oxygen:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Oxygen\", Helvetica, sans-serif', 'google', 0, 0),
(22, 'Poppins', 'poppins', '<link href=\"https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Poppins\", Helvetica, sans-serif', 'local', 1, 0),
(23, 'PT Sans', 'pt-sans', '<link href=\"https://fonts.googleapis.com/css?family=PT+Sans:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"PT Sans\", Helvetica, sans-serif', 'google', 0, 0),
(24, 'Raleway', 'raleway', '<link href=\"https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Raleway\", Helvetica, sans-serif', 'google', 0, 0),
(25, 'Roboto', 'roboto', '<link href=\"https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Roboto\", Helvetica, sans-serif', 'google', 0, 0),
(26, 'Roboto Condensed', 'roboto-condensed', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Condensed:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Condensed\", Helvetica, sans-serif', 'google', 0, 0),
(27, 'Roboto Slab', 'roboto-slab', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Slab\", Helvetica, sans-serif', 'google', 0, 0),
(28, 'Rokkitt', 'rokkitt', '<link href=\"https://fonts.googleapis.com/css?family=Rokkitt:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Rokkitt\", Helvetica, sans-serif', 'google', 0, 0),
(29, 'Source Sans Pro', 'source-sans-pro', '<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Source Sans Pro\", Helvetica, sans-serif', 'google', 0, 0),
(30, 'Titillium Web', 'titillium-web', '<link href=\"https://fonts.googleapis.com/css?family=Titillium+Web:300,400,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Titillium Web\", Helvetica, sans-serif', 'google', 0, 0),
(31, 'Ubuntu', 'ubuntu', '<link href=\"https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Ubuntu\", Helvetica, sans-serif', 'google', 0, 0),
(32, 'Verdana', 'verdana', NULL, 'font-family: Verdana, Helvetica, sans-serif', 'local', 0, 1),
(33, 'Work Sans', 'work-sans', '<link href=\"https://fonts.googleapis.com/css?family=Work+Sans:300,400,500,600&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\"> ', 'font-family: \"Work Sans\", Helvetica, sans-serif', 'google', 0, 0),
(34, 'Libre Baskerville', 'libre-baskerville', '<link href=\"https://fonts.googleapis.com/css?family=Libre+Baskerville:400,400i&display=swap&subset=latin-ext\" rel=\"stylesheet\"> ', 'font-family: \"Libre Baskerville\", Helvetica, sans-serif', 'google', 0, 0),
(35, 'Signika', 'signika', '<link href=\"https://fonts.googleapis.com/css2?family=Signika:wght@300;400;600;700&display=swap\" rel=\"stylesheet\">', 'font-family: \'Signika\', sans-serif;', 'google', 0, 0),
(36, 'Tajawal', 'tajawal', '<link href=\"https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap\" rel=\"stylesheet\">', 'font-family: \'Tajawal\', sans-serif;', 'google', 0, 0);";
    runQuery($sqlFonts);

    //delete routes
    runQuery("INSERT INTO `routes` (`route_key`, `route`) VALUES ('edit_profile', 'edit-profile')");
    runQuery("INSERT INTO `routes` (`route_key`, `route`) VALUES ('register_success', 'register-success')");
    runQuery("DELETE FROM routes WHERE `route_key`='conversation';");
    runQuery("DELETE FROM routes WHERE `route_key`='update_profile';");
    runQuery("DELETE FROM routes WHERE `route_key`='pending_products';");
    runQuery("DELETE FROM routes WHERE `route_key`='hidden_products';");
    runQuery("DELETE FROM routes WHERE `route_key`='drafts';");
    runQuery("DELETE FROM routes WHERE `route_key`='completed_sales';");
    runQuery("DELETE FROM routes WHERE `route_key`='expired_products';");
    runQuery("DELETE FROM routes WHERE `route_key`='cover_image';");
    runQuery("DELETE FROM routes WHERE `route_key`='sold_products';");
    runQuery("DELETE FROM routes WHERE `route_key`='cancelled_sales';");

    sleep(1);

    //add new translations
    $p = array();
    $p["cash_on_delivery_vendor_exp"] = "Sell your products with pay on delivery option";
    $p["fade"] = "Fade";
    $p["slide"] = "Slide";
    $p["mail_service"] = "Mail Service";
    $p["smtp"] = "SMTP";
    $p["mailjet_email_address"] = "Mailjet Email Address";
    $p["mailjet_email_address_exp"] = "The address you created your Mailjet account with";
    $p["generate_sitemap"] = "Generate Sitemap";
    $p["banner_desktop"] = "Desktop Banner";
    $p["banner_desktop_exp"] = "This ad will be displayed on screens larger than 992px";
    $p["banner_mobile"] = "Mobile Banner";
    $p["banner_mobile_exp"] = "This ad will be displayed on screens smaller than 992px";
    $p["ad_size"] = "Ad Size";
    $p["width"] = "Width";
    $p["height"] = "Height";
    $p["create_ad_exp"] = "If you don not have an ad code, you can create an ad code by selecting an image and adding an URL";
    $p["download_database_backup"] = "Download Database Backup";
    $p["activation_email_sent"] = "Activation email has been sent!";
    $p["warning_edit_profile_image"] = "Click on the save changes button after selecting your image";
    $p["cover_image_type"] = "Cover Image Type";
    $p["if_review_already_added"] = "If you have already added a review, your review will be updated.";
    $p["font_size"] = "Font Size";
    $p["show_customer_email_seller"] = "Show Customer Email to Seller";
    $p["show_customer_phone_number_seller"] = "Show Customer Phone Number to Seller";
    $p["accept_cookies"] = "Accept Cookies";
    $p["custom_header_codes"] = "Custom Header Codes";
    $p["custom_header_codes_exp"] = "These codes will be added to the header of the site";
    $p["custom_footer_codes"] = "Custom Footer Codes";
    $p["custom_footer_codes_exp"] = "These codes will be added to the footer of the site";
    $p["highest_rating"] = "Highest Rating";
    addTranslations($p);

    //delete old translations
    runQuery("DELETE FROM language_translations WHERE `label`='blog_post_details_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='blog_post_details_sidebar_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='completed_payouts';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_category';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_custom_field';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_language';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_option';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_page';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_post';");
    runQuery("DELETE FROM language_translations WHERE `label`='cover_image';");
    runQuery("DELETE FROM language_translations WHERE `label`='custom_css_codes';");
    runQuery("DELETE FROM language_translations WHERE `label`='custom_css_codes_exp';");
    runQuery("DELETE FROM language_translations WHERE `label`='custom_javascript_codes';");
    runQuery("DELETE FROM language_translations WHERE `label`='custom_javascript_codes_exp';");
    runQuery("DELETE FROM language_translations WHERE `label`='download_sitemap';");
    runQuery("DELETE FROM language_translations WHERE `label`='mail_library';");
    runQuery("DELETE FROM language_translations WHERE `label`='middle';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_category_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_category_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_custom_field_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_custom_field_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_language_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_language_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_option_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_page_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_page_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_post_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_post_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_product_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_slider_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_slider_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_user_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='products_sidebar_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='product_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='product_cache_system';");
    runQuery("DELETE FROM language_translations WHERE `label`='profile_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='profile_sidebar_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='static_content_cache_system';");
    runQuery("DELETE FROM language_translations WHERE `label`='update_sitemap';");
    runQuery("DELETE FROM language_translations WHERE `label`='warning_static_content_cache_system';");

    runQuery("UPDATE general_settings SET watermark_vrt_alignment='center' WHERE id='1'");
    runQuery("UPDATE general_settings SET watermark_hor_alignment='center' WHERE id='1'");
    runQuery("UPDATE general_settings SET version='2.3' WHERE id='1'");
}

function addTranslations($translations)
{
    $languages = runQuery("SELECT * FROM languages;");
    if (!empty($languages->num_rows)) {
        while ($language = mysqli_fetch_array($languages)) {
            foreach ($translations as $key => $value) {
                $trans = runQuery("SELECT * FROM language_translations WHERE label ='" . $key . "' AND lang_id = " . $language['id']);
                if (empty($trans->num_rows)) {
                    runQuery("INSERT INTO `language_translations` (`lang_id`, `label`, `translation`) VALUES (" . $language['id'] . ", '" . $key . "', '" . $value . "');");
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modesy - Update Wizard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,700" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #444 !important;
            font-size: 14px;
            background: #007991;
            background: -webkit-linear-gradient(to left, #007991, #6fe7c2);
            background: linear-gradient(to left, #007991, #6fe7c2);
        }

        .logo-cnt {
            text-align: center;
            color: #fff;
            padding: 60px 0 60px 0;
        }

        .logo-cnt .logo {
            font-size: 42px;
            line-height: 42px;
        }

        .logo-cnt p {
            font-size: 22px;
        }

        .install-box {
            width: 100%;
            padding: 30px;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            margin: auto;
            background-color: #fff;
            border-radius: 4px;
            display: block;
            float: left;
            margin-bottom: 100px;
        }

        .form-input {
            box-shadow: none !important;
            border: 1px solid #ddd;
            height: 44px;
            line-height: 44px;
            padding: 0 20px;
        }

        .form-input:focus {
            border-color: #239CA1 !important;
        }

        .btn-custom {
            background-color: #239CA1 !important;
            border-color: #239CA1 !important;
            border: 0 none;
            border-radius: 4px;
            box-shadow: none;
            color: #fff !important;
            font-size: 16px;
            font-weight: 300;
            height: 40px;
            line-height: 40px;
            margin: 0;
            min-width: 105px;
            padding: 0 20px;
            text-shadow: none;
            vertical-align: middle;
        }

        .btn-custom:hover, .btn-custom:active, .btn-custom:focus {
            background-color: #239CA1;
            border-color: #239CA1;
            opacity: .8;
        }

        .tab-content {
            width: 100%;
            float: left;
            display: block;
        }

        .tab-footer {
            width: 100%;
            float: left;
            display: block;
        }

        .buttons {
            display: block;
            float: left;
            width: 100%;
            margin-top: 30px;
        }

        .title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            margin-top: 0;
            text-align: center;
        }

        .sub-title {
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 30px;
            margin-top: 0;
            text-align: center;
        }

        .alert {
            text-align: center;
        }

        .alert strong {
            font-weight: 500 !important;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-sm-12 col-md-offset-2">
            <div class="row">
                <div class="col-sm-12 logo-cnt">
                    <h1>Modesy</h1>
                    <p>Welcome to the Update Wizard</p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="install-box">
                        <h2 class="title">Update from v2.1 to v2.3</h2>
                        <br><br>
                        <div class="messages">
                            <?php if (!empty($error)) { ?>
                                <div class="alert alert-danger">
                                    <strong><?= $error; ?></strong>
                                </div>
                            <?php } ?>
                            <?php if (!empty($success)) { ?>
                                <div class="alert alert-success">
                                    <strong><?= $success; ?></strong>
                                    <style>.alert-info {
                                            display: none;
                                        }</style>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="step-contents">
                            <div class="tab-1">
                                <?php if (empty($success)): ?>
                                    <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                                        <div class="tab-footer text-center">
                                            <button type="submit" name="btn_submit" class="btn-custom">Update My Database</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>