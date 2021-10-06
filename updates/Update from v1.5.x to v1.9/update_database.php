<?php
define('BASEPATH', "/");
define('ENVIRONMENT', 'production');
require_once "application/config/database.php";
$license_code = '';
$purchase_code = '';
if (!file_exists('old')) {
    echo '<strong>"old" folder not found!</strong><br>';
    echo 'The script will move all language translations from "old/application/language" folder to the database. So you need to create a folder named "old" for your old files. 
    Please change the name of your folder to the "old".';
    exit();
}
if (file_exists('license.php')) {
    include 'license.php';
}

if (!function_exists('curl_init')) {
    $error = 'cURL is not available on your server! Please enable cURL to continue the installation. You can read the documentation for more information.';
    exit();
}

//set database credentials
$database = $db['default'];
$db_host = $database['hostname'];
$db_name = $database['database'];
$db_user = $database['username'];
$db_password = $database['password'];

/* Connect */
$connection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
$connection->query("SET CHARACTER SET utf8");
$connection->query("SET NAMES utf8");
if (!$connection) {
    $error = "Connect failed! Please check your database credentials.";
}

if (isset($_POST["btn_submit"])) {
	$license_code = 'license_code';
	$purchase_code = 'purchase_code';
    update($license_code, $purchase_code, $connection);
    sleep(1);
    /* close connection */
    mysqli_close($connection);
    $success = 'The update has been successfully completed! Please delete the "update_database.php" file.';

}

function update($license_code, $purchase_code, $connection)
{
    update_15_to_16($license_code, $purchase_code, $connection);
    sleep(1);
    update_16_to_17($license_code, $purchase_code, $connection);
    sleep(1);
    update_17_to_18($license_code, $purchase_code, $connection);
    sleep(1);
    update_18_to_19($license_code, $purchase_code, $connection);
    add_new_translations($license_code, $purchase_code, $connection);
}

function update_15_to_16($license_code, $purchase_code, $connection)
{
    $sql_gallery_albums = "CREATE TABLE `gallery_albums` (
        `id` INT AUTO_INCREMENT PRIMARY KEY, 
        `lang_id` int(11) DEFAULT '1',
        `name` varchar(255) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $sql_post_gallery_items = "CREATE TABLE `post_gallery_items` (
          `id` INT AUTO_INCREMENT PRIMARY KEY, 
          `post_id` int(11) DEFAULT NULL,
          `title` varchar(500) DEFAULT NULL,
          `content` text,
          `image` varchar(255) DEFAULT NULL,
          `image_large` varchar(255) DEFAULT NULL,
          `image_description` varchar(255) DEFAULT NULL,
          `item_order` smallint(6) DEFAULT NULL,
          `is_collapsed` tinyint(1) DEFAULT '0'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $sql_post_ordered_list_items = "CREATE TABLE `post_ordered_list_items` (
           `id` INT AUTO_INCREMENT PRIMARY KEY, 
          `post_id` int(11) DEFAULT NULL,
          `title` varchar(500) DEFAULT NULL,
          `content` text,
          `image` varchar(255) DEFAULT NULL,
          `image_large` varchar(255) DEFAULT NULL,
          `image_description` varchar(255) DEFAULT NULL,
          `item_order` smallint(6) DEFAULT NULL,
          `is_collapsed` tinyint(1) DEFAULT '0'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    /* update database */
    mysqli_query($connection, $sql_gallery_albums);
    mysqli_query($connection, $sql_post_gallery_items);
    mysqli_query($connection, $sql_post_ordered_list_items);
    sleep(1);
    mysqli_query($connection, "ALTER TABLE comments ADD COLUMN `email` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE comments ADD COLUMN `name` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE comments ADD COLUMN `ip_address` VARCHAR(100);");
    mysqli_query($connection, "ALTER TABLE comments ADD COLUMN `like_count` INT DEFAULT 0;");
    mysqli_query($connection, "DROP TABLE comment_likes;");
    mysqli_query($connection, "ALTER TABLE gallery ADD COLUMN `album_id` INT DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE gallery ADD COLUMN `is_album_cover` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE gallery_categories ADD COLUMN `album_id` INT DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `mail_library` VARCHAR(100) DEFAULT 'swift';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `google_client_id` VARCHAR(500);");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `google_client_secret` VARCHAR(500);");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `vk_app_id` VARCHAR(500);");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `vk_secure_key` VARCHAR(500);");
    mysqli_query($connection, "ALTER TABLE general_settings DROP COLUMN `google_app_name`;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `sort_slider_posts` VARCHAR(100) DEFAULT 'by_slider_order';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `sort_featured_posts` VARCHAR(100) DEFAULT 'by_featured_order';");
    mysqli_query($connection, "ALTER TABLE general_settings DROP COLUMN `copyright`;");
    mysqli_query($connection, "ALTER TABLE post_images DROP COLUMN `created_at`;");
    mysqli_query($connection, "ALTER TABLE tags DROP COLUMN `created_at`;");
    mysqli_query($connection, "ALTER TABLE images ADD COLUMN `image_mime` VARCHAR(50) DEFAULT 'jpg';");
    mysqli_query($connection, "ALTER TABLE newsletters ADD COLUMN `token` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE posts ADD COLUMN `title_hash` VARCHAR(500);");
    mysqli_query($connection, "ALTER TABLE posts ADD COLUMN `image_mime` VARCHAR(20) DEFAULT 'jpg';");
    mysqli_query($connection, "ALTER TABLE posts ADD COLUMN `is_scheduled` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE posts ADD COLUMN `show_item_numbers` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE rss_feeds ADD COLUMN `image_mime` VARCHAR(20) DEFAULT 'jpg';");
    mysqli_query($connection, "ALTER TABLE settings DROP COLUMN `google_url`;");
    mysqli_query($connection, "ALTER TABLE settings ADD COLUMN `telegram_url` VARCHAR(500);");
    mysqli_query($connection, "ALTER TABLE users ADD COLUMN `vk_id` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE users ADD COLUMN `telegram_url` VARCHAR(500);");
    mysqli_query($connection, "ALTER TABLE users ADD COLUMN `show_email_on_profile` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE users ADD COLUMN `show_rss_feeds` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE users DROP COLUMN `google_url`;");

    $del = "DELETE FROM pages WHERE slug='register';";
    mysqli_query($connection, $del);
    $del = "DELETE FROM pages WHERE slug='reset-password';";
    mysqli_query($connection, $del);
    $del = "DELETE FROM pages WHERE slug='posts';";
    mysqli_query($connection, $del);
    $del = "DELETE FROM pages WHERE slug='rss-feeds';";
    mysqli_query($connection, $del);
    $del = "DELETE FROM pages WHERE slug='reading-list';";
    mysqli_query($connection, $del);

    $sql = "SELECT * FROM languages";
    $result = mysqli_query($connection, $sql);
    while ($row = mysqli_fetch_array($result)) {
        if (!empty($row['id'])) {
            $insert = "INSERT INTO pages (`lang_id`, `title`, `slug`, `description`, `keywords`, `is_custom`, `page_content`, `page_order`, `visibility`, `title_active`, `breadcrumb_active`, `right_column_active`, `need_auth`, `location`, `parent_id`, `page_type`) 
                VALUES ('" . $row['id'] . "', 'Terms & Conditions', 'terms-conditions', 'Varient Terms Conditions Page','varient, terms, conditions', 0, NULL, 1, 1, 1, 1, 0, 0, 'footer', 0, 'page')";
            mysqli_query($connection, $insert);

            $insert = "INSERT INTO  gallery_albums (`lang_id`, `name`) 
                VALUES ('" . $row['id'] . "', 'Album 1')";
            mysqli_query($connection, $insert);
        }
    }
}

function update_16_to_17($license_code, $purchase_code, $connection)
{
    $table_sessions = "CREATE TABLE IF NOT EXISTS `ci_sessions` (
    `id` varchar(128) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
    `data` blob NOT NULL,
    KEY `ci_sessions_timestamp` (`timestamp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_files = "CREATE TABLE `files` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `file_name` varchar(255) DEFAULT NULL,
      `file_path` varchar(255) DEFAULT NULL,
      `user_id` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_fonts = "CREATE TABLE `fonts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `font_name` varchar(255) DEFAULT NULL,
    `font_url` varchar(2000) DEFAULT NULL,
    `font_family` varchar(500) DEFAULT NULL,
    `is_default` tinyint(1) DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_post_files = "CREATE TABLE `post_files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` int(11) DEFAULT NULL,
    `file_id` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_quiz_answers = "CREATE TABLE `quiz_answers` (
     `id` INT AUTO_INCREMENT PRIMARY KEY,
      `question_id` int(11) DEFAULT NULL,
      `image_path` varchar(255) DEFAULT NULL,
      `answer_text` varchar(500) DEFAULT NULL,
      `is_correct` tinyint(1) DEFAULT NULL,
      `assigned_result_id` int(11) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_quiz_images = "CREATE TABLE `quiz_images` (
     `id` INT AUTO_INCREMENT PRIMARY KEY,
      `image_default` varchar(255) DEFAULT NULL,
      `image_small` varchar(255) DEFAULT NULL,
      `file_name` varchar(255) NOT NULL,
      `image_mime` varchar(20) DEFAULT 'jpg',
      `user_id` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_quiz_questions = "CREATE TABLE `quiz_questions` (
     `id` INT AUTO_INCREMENT PRIMARY KEY,
      `post_id` int(11) DEFAULT NULL,
      `question` varchar(500) DEFAULT NULL,
      `image_path` varchar(255) DEFAULT NULL,
      `description` text,
      `question_order` int(11) DEFAULT '1',
      `answer_format` varchar(30) DEFAULT 'small_image'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_quiz_results = "CREATE TABLE `quiz_results` (
     `id` INT AUTO_INCREMENT PRIMARY KEY,
      `post_id` int(11) DEFAULT NULL,
      `result_title` varchar(500) DEFAULT NULL,
      `image_path` varchar(255) DEFAULT NULL,
      `description` text,
      `min_correct_count` mediumint(9) DEFAULT NULL,
      `max_correct_count` mediumint(9) DEFAULT NULL,
      `result_order` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_routes = "CREATE TABLE `routes` (
     `id` INT AUTO_INCREMENT PRIMARY KEY,
      `admin` varchar(100) DEFAULT 'admin',
      `profile` varchar(100) DEFAULT 'profile',
      `tag` varchar(100) DEFAULT 'tag',
      `reading_list` varchar(100) DEFAULT 'reading-list',
      `settings` varchar(100) DEFAULT 'settings',
      `social_accounts` varchar(100) DEFAULT 'social-accounts',
      `preferences` varchar(100) DEFAULT 'preferences',
      `visual_settings` varchar(100) DEFAULT 'visual-settings',
      `change_password` varchar(100) DEFAULT 'change-password',
      `forgot_password` varchar(100) DEFAULT 'forgot-password',
      `reset_password` varchar(100) DEFAULT 'reset-password',
      `register` varchar(100) DEFAULT 'register',
      `posts` varchar(100) DEFAULT 'posts',
      `search` varchar(100) DEFAULT 'search',
      `rss_feeds` varchar(100) DEFAULT 'rss-feeds',
      `gallery_album` varchar(100) DEFAULT 'gallery-album',
      `logout` varchar(100) DEFAULT 'logout'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    mysqli_query($connection, $table_sessions);
    mysqli_query($connection, $table_files);
    mysqli_query($connection, $table_fonts);
    mysqli_query($connection, $table_post_files);
    mysqli_query($connection, $table_quiz_answers);
    mysqli_query($connection, $table_quiz_images);
    mysqli_query($connection, $table_quiz_questions);
    mysqli_query($connection, $table_quiz_results);
    mysqli_query($connection, $table_routes);
    sleep(1);
    mysqli_query($connection, "ALTER TABLE audios DROP COLUMN `musician`;");
    mysqli_query($connection, "ALTER TABLE comments ADD COLUMN `status` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings DROP COLUMN `site_color`;");
    mysqli_query($connection, "ALTER TABLE general_settings DROP COLUMN `primary_font`;");
    mysqli_query($connection, "ALTER TABLE general_settings DROP COLUMN `secondary_font`;");
    mysqli_query($connection, "ALTER TABLE general_settings DROP COLUMN `tertiary_font`;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `comment_approval_system` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings CHANGE `head_code` `custom_css_codes` MEDIUMTEXT;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `custom_javascript_codes` MEDIUMTEXT;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `adsense_activation_code` TEXT;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `audio_download_button` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `text_editor_lang` VARCHAR(30) DEFAULT 'en';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `show_home_link` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `post_format_article` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `post_format_gallery` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `post_format_sorted_list` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `post_format_video` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `post_format_audio` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `post_format_trivia_quiz` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `post_format_personality_quiz` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `maintenance_mode_title` VARCHAR(500) DEFAULT 'Coming Soon!';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `maintenance_mode_description` VARCHAR(5000) DEFAULT \"Our website is under construction. We'll be here soon with our new awesome site.\";");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `maintenance_mode_status` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `sitemap_frequency` VARCHAR(30) DEFAULT 'monthly';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `sitemap_last_modification` VARCHAR(30) DEFAULT 'server_response';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `sitemap_priority` VARCHAR(30) DEFAULT 'automatically';");
    mysqli_query($connection, "ALTER TABLE general_settings DROP COLUMN `created_at`;");
    mysqli_query($connection, "ALTER TABLE images ADD COLUMN `file_name` VARCHAR(255);");
    mysqli_query($connection, "RENAME TABLE newsletters TO subscribers;");
    mysqli_query($connection, "ALTER TABLE pages ADD COLUMN `page_default_name` VARCHAR(100);");
    mysqli_query($connection, "ALTER TABLE posts CHANGE `hit` `pageviews` INT(11) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE posts ADD COLUMN `updated_at` TIMESTAMP NULL;");
    mysqli_query($connection, "ALTER TABLE posts ADD COLUMN `video_url` VARCHAR(2000);");
    mysqli_query($connection, "ALTER TABLE post_gallery_items DROP COLUMN `is_collapsed`;");
    mysqli_query($connection, "ALTER TABLE post_ordered_list_items DROP COLUMN `is_collapsed`;");
    mysqli_query($connection, "RENAME TABLE post_ordered_list_items TO post_sorted_list_items;");
    mysqli_query($connection, "RENAME TABLE post_hits TO post_pageviews;");
    mysqli_query($connection, "ALTER TABLE post_pageviews ADD COLUMN `ip_address` VARCHAR(30);");
    mysqli_query($connection, "ALTER TABLE rss_feeds ADD COLUMN `image_saving_method` VARCHAR(30) DEFAULT 'url';");
    mysqli_query($connection, "ALTER TABLE rss_feeds DROP COLUMN `image_big`;");
    mysqli_query($connection, "ALTER TABLE rss_feeds DROP COLUMN `image_default`;");
    mysqli_query($connection, "ALTER TABLE rss_feeds DROP COLUMN `image_slider`;");
    mysqli_query($connection, "ALTER TABLE rss_feeds DROP COLUMN `image_mid`;");
    mysqli_query($connection, "ALTER TABLE rss_feeds DROP COLUMN `image_small`;");
    mysqli_query($connection, "ALTER TABLE rss_feeds DROP COLUMN `image_mime`;");
    mysqli_query($connection, "ALTER TABLE settings ADD COLUMN `primary_font` SMALLINT(6) DEFAULT 19;");
    mysqli_query($connection, "ALTER TABLE settings ADD COLUMN `secondary_font` SMALLINT(6) DEFAULT 25;");
    mysqli_query($connection, "ALTER TABLE settings ADD COLUMN `tertiary_font` SMALLINT(6) DEFAULT 32;");
    mysqli_query($connection, "ALTER TABLE settings DROP COLUMN IF EXISTS `created_at`;");
    mysqli_query($connection, "ALTER TABLE users ADD COLUMN `site_mode` VARCHAR(10);");
    mysqli_query($connection, "ALTER TABLE users ADD COLUMN `site_color` VARCHAR(30);");
    mysqli_query($connection, "ALTER TABLE visual_settings ADD COLUMN `dark_mode` TINYINT(1) DEFAULT 0;");

    //add routes
    $sql_routes = "INSERT INTO `routes` (`id`, `admin`, `profile`, `tag`, `reading_list`, `settings`, `social_accounts`, `preferences`, `visual_settings`, `change_password`, `forgot_password`, `reset_password`, `register`, `posts`, `search`, `rss_feeds`, `gallery_album`, `logout`) VALUES
(1, 'admin', 'profile', 'tag', 'reading-list', 'settings', 'social-accounts', 'preferences', 'visual-settings', 'change-password', 'forgot-password', 'reset-password', 'register', 'posts', 'search', 'rss-feeds', 'gallery-album', 'logout');";
    mysqli_query($connection, $sql_routes);

    //add fonts
    $sql_fonts="INSERT INTO `fonts` (`id`, `font_name`, `font_url`, `font_family`, `is_default`) VALUES
(1, 'Arial', NULL, 'font-family: Arial, Helvetica, sans-serif', 1),
(2, 'Arvo', '<link href=\"https://fonts.googleapis.com/css?family=Arvo:400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Arvo\", Helvetica, sans-serif', 0),
(3, 'Averia Libre', '<link href=\"https://fonts.googleapis.com/css?family=Averia+Libre:300,400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Averia Libre\", Helvetica, sans-serif', 0),
(4, 'Bitter', '<link href=\"https://fonts.googleapis.com/css?family=Bitter:400,400i,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Bitter\", Helvetica, sans-serif', 0),
(5, 'Cabin', '<link href=\"https://fonts.googleapis.com/css?family=Cabin:400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Cabin\", Helvetica, sans-serif', 0),
(6, 'Cherry Swash', '<link href=\"https://fonts.googleapis.com/css?family=Cherry+Swash:400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Cherry Swash\", Helvetica, sans-serif', 0),
(7, 'Encode Sans', '<link href=\"https://fonts.googleapis.com/css?family=Encode+Sans:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Encode Sans\", Helvetica, sans-serif', 0),
(8, 'Helvetica', NULL, 'font-family: Helvetica, sans-serif', 1),
(9, 'Hind', '<link href=\"https://fonts.googleapis.com/css?family=Hind:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Hind\", Helvetica, sans-serif', 0),
(10, 'Josefin Sans', '<link href=\"https://fonts.googleapis.com/css?family=Josefin+Sans:300,400,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Josefin Sans\", Helvetica, sans-serif', 0),
(11, 'Kalam', '<link href=\"https://fonts.googleapis.com/css?family=Kalam:300,400,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Kalam\", Helvetica, sans-serif', 0),
(12, 'Khula', '<link href=\"https://fonts.googleapis.com/css?family=Khula:300,400,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Khula\", Helvetica, sans-serif', 0),
(13, 'Lato', '<link href=\"https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Lato\", Helvetica, sans-serif', 0),
(14, 'Lora', '<link href=\"https://fonts.googleapis.com/css?family=Lora:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Lora\", Helvetica, sans-serif', 0),
(15, 'Merriweather', '<link href=\"https://fonts.googleapis.com/css?family=Merriweather:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Merriweather\", Helvetica, sans-serif', 0),
(16, 'Montserrat', '<link href=\"https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Montserrat\", Helvetica, sans-serif', 0),
(17, 'Mukta', '<link href=\"https://fonts.googleapis.com/css?family=Mukta:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Mukta\", Helvetica, sans-serif', 0),
(18, 'Nunito', '<link href=\"https://fonts.googleapis.com/css?family=Nunito:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Nunito\", Helvetica, sans-serif', 0),
(19, 'Open Sans', '<link href=\"https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Open Sans\", Helvetica, sans-serif', 0),
(20, 'Oswald', '<link href=\"https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Oswald\", Helvetica, sans-serif', 0),
(21, 'Oxygen', '<link href=\"https://fonts.googleapis.com/css?family=Oxygen:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Oxygen\", Helvetica, sans-serif', 0),
(22, 'Poppins', '<link href=\"https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Poppins\", Helvetica, sans-serif', 0),
(23, 'PT Sans', '<link href=\"https://fonts.googleapis.com/css?family=PT+Sans:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"PT Sans\", Helvetica, sans-serif', 0),
(24, 'Raleway', '<link href=\"https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Raleway\", Helvetica, sans-serif', 0),
(25, 'Roboto', '<link href=\"https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Roboto\", Helvetica, sans-serif', 0),
(26, 'Roboto Condensed', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Condensed:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Condensed\", Helvetica, sans-serif', 0),
(27, 'Roboto Slab', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Slab\", Helvetica, sans-serif', 0),
(28, 'Rokkitt', '<link href=\"https://fonts.googleapis.com/css?family=Rokkitt:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Rokkitt\", Helvetica, sans-serif', 0),
(29, 'Source Sans Pro', '<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Source Sans Pro\", Helvetica, sans-serif', 0),
(30, 'Titillium Web', '<link href=\"https://fonts.googleapis.com/css?family=Titillium+Web:300,400,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Titillium Web\", Helvetica, sans-serif', 0),
(31, 'Ubuntu', '<link href=\"https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Ubuntu\", Helvetica, sans-serif', 0),
(32, 'Verdana', NULL, 'font-family: Verdana, Helvetica, sans-serif', 1);";
    mysqli_query($connection, $sql_fonts);

    //update page default names
    $sql = "SELECT * FROM pages ORDER BY id";
    $result = mysqli_query($connection, $sql);
    while ($row = mysqli_fetch_array($result)) {
        $page_default_name = "";
        if ($row['slug'] == 'contact') {
            $page_default_name = 'contact';
        }
        if ($row['slug'] == 'gallery') {
            $page_default_name = 'gallery';
        }
        if ($row['slug'] == 'terms-conditions') {
            $page_default_name = 'terms_conditions';
        }
        if (!empty($page_default_name)) {
            mysqli_query($connection, "UPDATE pages SET `page_default_name`='" . $page_default_name . "' WHERE id=" . $row['id']);
        }
    }

    //update posts
    $sql = "SELECT * FROM posts ORDER BY id";
    $result = mysqli_query($connection, $sql);
    while ($row = mysqli_fetch_array($result)) {
        $cat_id = 0;
        if (!empty($row['subcategory_id'])) {
            $cat_id = $row['subcategory_id'];
        } elseif (!empty($row['category_id'])) {
            $cat_id = $row['category_id'];
        }
        $post_type = $row['post_type'];
        if ($post_type == "post") {
            $post_type = 'article';
        }
        if ($post_type == "ordered_list") {
            $post_type = 'sorted_list';
        }
        mysqli_query($connection, "UPDATE posts SET `category_id`=" . $cat_id . ", `post_type`='" . $post_type . "' WHERE id=" . $row['id']);
    }
    mysqli_query($connection, "ALTER TABLE posts DROP COLUMN `subcategory_id`;");

    //update rss feeds
    $sql = "SELECT * FROM rss_feeds ORDER BY id";
    $result = mysqli_query($connection, $sql);
    while ($row = mysqli_fetch_array($result)) {
        $cat_id = 0;
        if (!empty($row['subcategory_id'])) {
            $cat_id = $row['subcategory_id'];
        } elseif (!empty($row['category_id'])) {
            $cat_id = $row['category_id'];
        }
        mysqli_query($connection, "UPDATE rss_feeds SET `category_id`=" . $cat_id . " WHERE id=" . $row['id']);
    }
    sleep(1);
    mysqli_query($connection, "ALTER TABLE rss_feeds DROP COLUMN `subcategory_id`;");

    //add keys
    mysqli_query($connection, "ALTER TABLE comments ADD INDEX idx_parent_id (parent_id);");
    mysqli_query($connection, "ALTER TABLE comments ADD INDEX idx_post_id (post_id);");
    mysqli_query($connection, "ALTER TABLE comments ADD INDEX idx_status (status);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_lang_id (lang_id);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_category_id (category_id);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_is_slider (is_slider);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_is_featured (is_featured);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_is_recommended (is_recommended);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_is_breaking (is_breaking);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_is_scheduled (is_scheduled);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_visibility (visibility);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_user_id (user_id);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_status (status);");
    mysqli_query($connection, "ALTER TABLE posts ADD INDEX idx_created_at (created_at)");
    mysqli_query($connection, "ALTER TABLE post_pageviews ADD INDEX idx_post_id (post_id)");
    mysqli_query($connection, "ALTER TABLE post_pageviews ADD INDEX idx_created_at (created_at)");
    mysqli_query($connection, "ALTER TABLE tags ADD INDEX idx_post_id (post_id)");
}

function update_17_to_18($license_code, $purchase_code, $connection)
{
    $version = '1.7';
    $sql = "SELECT * FROM general_settings WHERE id = 1";
    $result = mysqli_query($connection, $sql);
    while ($row = mysqli_fetch_array($result)) {
        if (!empty($row['version'])) {
            if ($row['version'] == '1.7.1') {
                $version = '1.7.1';
            }
        }
    }

    if ($version == '1.7') {
        $table_post_pageviews_week = "CREATE TABLE `post_pageviews_week` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `post_id` int(11) DEFAULT NULL,
            `ip_address` varchar(30) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        mysqli_query($connection, $table_post_pageviews_week);
        mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `version` VARCHAR(30) DEFAULT '1.8.1';");
        mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `last_popular_post_update` TIMESTAMP;");
        mysqli_query($connection, "ALTER TABLE post_pageviews_week ADD INDEX idx_post_id (post_id)");
        mysqli_query($connection, "ALTER TABLE post_pageviews_week ADD INDEX idx_created_at (created_at)");
        mysqli_query($connection, "RENAME TABLE post_pageviews TO post_pageviews_month;");
    }


    $table_language_translations = "CREATE TABLE `language_translations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lang_id` smallint(6) DEFAULT NULL,
    `label` varchar(255) DEFAULT NULL,
    `translation` varchar(500) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_payouts = "CREATE TABLE `payouts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` int(11) DEFAULT NULL,
    `username` varchar(100) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `amount` double NOT NULL,
    `payout_method` varchar(50) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $table_user_payout_accounts = "CREATE TABLE `user_payout_accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` int(11) DEFAULT NULL,
    `payout_paypal_email` varchar(255) DEFAULT NULL,
    `iban_full_name` varchar(255) DEFAULT NULL,
    `iban_country` varchar(100) DEFAULT NULL,
    `iban_bank_name` varchar(255) DEFAULT NULL,
    `iban_number` varchar(500) DEFAULT NULL,
    `swift_full_name` varchar(255) DEFAULT NULL,
    `swift_address` varchar(500) DEFAULT NULL,
    `swift_state` varchar(255) DEFAULT NULL,
    `swift_city` varchar(255) DEFAULT NULL,
    `swift_postcode` varchar(100) DEFAULT NULL,
    `swift_country` varchar(100) DEFAULT NULL,
    `swift_bank_account_holder_name` varchar(255) DEFAULT NULL,
    `swift_iban` varchar(255) DEFAULT NULL,
    `swift_code` varchar(255) DEFAULT NULL,
    `swift_bank_name` varchar(255) DEFAULT NULL,
    `swift_bank_branch_city` varchar(255) DEFAULT NULL,
    `swift_bank_branch_country` varchar(100) DEFAULT NULL,
    `default_payout_account` varchar(30) NOT NULL DEFAULT 'paypal'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


    mysqli_query($connection, $table_language_translations);
    mysqli_query($connection, $table_payouts);
    mysqli_query($connection, $table_user_payout_accounts);
    sleep(1);

    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `pwa_status` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `show_latest_posts_on_slider` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `show_latest_posts_on_featured` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE general_settings DROP COLUMN `text_editor_lang`;");
    mysqli_query($connection, "ALTER TABLE general_settings DROP COLUMN `last_popular_post_update`;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `show_user_email_on_profile` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `reward_system_status` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `reward_amount` DOUBLE DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `currency_name` VARCHAR(100) DEFAULT 'US Dollar';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `currency_symbol` VARCHAR(10) DEFAULT '$';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `currency_format` VARCHAR(10) DEFAULT 'us';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `currency_symbol_format` VARCHAR(10) DEFAULT 'left';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `payout_paypal_status` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `payout_iban_status` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `payout_swift_status` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `cookie_prefix` VARCHAR(50);");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `last_cron_update` TIMESTAMP;");

    mysqli_query($connection, "ALTER TABLE images DROP COLUMN `lang_id`;");
    mysqli_query($connection, "ALTER TABLE languages ADD COLUMN `text_editor_lang` VARCHAR(30) DEFAULT 'en';");

    mysqli_query($connection, "ALTER TABLE post_pageviews_month ADD COLUMN `post_user_id` INT;");
    mysqli_query($connection, "ALTER TABLE post_pageviews_month ADD COLUMN `user_agent` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE post_pageviews_month ADD COLUMN `reward_amount` DOUBLE DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE post_pageviews_week ADD COLUMN `post_user_id` INT;");
    mysqli_query($connection, "ALTER TABLE post_pageviews_week ADD COLUMN `user_agent` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE post_pageviews_week ADD COLUMN `reward_amount` DOUBLE DEFAULT 0;");

    mysqli_query($connection, "ALTER TABLE roles_permissions ADD COLUMN `reward_system` TINYINT(1) DEFAULT 1;");

    mysqli_query($connection, "ALTER TABLE routes ADD COLUMN `delete_account` VARCHAR(100) DEFAULT 'delete-account';");
    mysqli_query($connection, "ALTER TABLE routes ADD COLUMN `earnings` VARCHAR(100) DEFAULT 'earnings';");
    mysqli_query($connection, "ALTER TABLE routes ADD COLUMN `payouts` VARCHAR(100) DEFAULT 'payouts';");
    mysqli_query($connection, "ALTER TABLE routes ADD COLUMN `set_payout_account` VARCHAR(100) DEFAULT 'set-payout-account';");

    mysqli_query($connection, "ALTER TABLE rss_feeds ADD COLUMN `is_cron_updated` TINYINT(1) DEFAULT 0;");

    mysqli_query($connection, "ALTER TABLE users ADD COLUMN `reward_system_enabled` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE users ADD COLUMN `balance` DOUBLE DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE users ADD COLUMN `total_pageviews` INT DEFAULT 0;");

    //update version and add cookie prefix
    $cookie_prefix = uniqid();
    mysqli_query($connection, "UPDATE general_settings SET version='1.8.1', cookie_prefix='" . $cookie_prefix . "' WHERE id='1'");
    //update roles
    mysqli_query($connection, "UPDATE roles_permissions SET reward_system = 0 WHERE role = 'author' OR role = 'user'");

    //add language translations
    $sql = "SELECT * FROM languages ORDER BY id";
    $result = mysqli_query($connection, $sql);
    while ($row = mysqli_fetch_array($result)) {
        $path = "old/application/language/" . $row["folder_name"] . "/site_lang.php";
        if (file_exists($path)) {
            include $path;
            if (!empty($lang)) {
                foreach ($lang as $key => $value) {
                    $insert_translation = "INSERT INTO `language_translations` (`lang_id`, `label`, `translation`) 
                    VALUES (" . $row["id"] . ", '" . $key . "' , '" . $value . "')";
                    mysqli_query($connection, $insert_translation);
                }
            }
        }
    }
    mysqli_query($connection, "ALTER TABLE languages DROP COLUMN `folder_name`;");
    //add index
    mysqli_query($connection, "ALTER TABLE language_translations ADD INDEX idx_lang_id (lang_id);");
    mysqli_query($connection, "ALTER TABLE user_payout_accounts ADD INDEX idx_user_id (user_id);");
}

function update_18_to_19($license_code, $purchase_code, $connection)
{
    mysqli_query($connection, "ALTER TABLE audios ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE files ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE gallery ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `mail_encryption` VARCHAR(100) DEFAULT 'tls';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `mail_reply_to` VARCHAR(255) DEFAULT 'noreply@domain.com';");
    mysqli_query($connection, "ALTER TABLE general_settings CHANGE `newsletter` `newsletter_status` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `newsletter_popup` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `aws_key` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `aws_secret` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `aws_bucket` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `aws_region` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `aws_base_url` VARCHAR(255);");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `auto_post_deletion` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `auto_post_deletion_days` smallint(6) DEFAULT 30;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `auto_post_deletion_delete_all` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `redirect_rss_posts_to_original` TINYINT(1) DEFAULT 0;");
    mysqli_query($connection, "ALTER TABLE images ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE posts ADD COLUMN `image_storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE posts ADD COLUMN `video_storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE post_gallery_items ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE post_images ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE post_sorted_list_items ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE quiz_answers ADD COLUMN `image_storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE quiz_images ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE quiz_questions ADD COLUMN `image_storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE quiz_results ADD COLUMN `image_storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE rss_feeds ADD COLUMN `generate_keywords_from_title` TINYINT(1) DEFAULT 1;");
    mysqli_query($connection, "ALTER TABLE users DROP COLUMN `site_mode`;");
    mysqli_query($connection, "ALTER TABLE users DROP COLUMN `site_color`;");
    mysqli_query($connection, "ALTER TABLE videos ADD COLUMN `storage` VARCHAR(20) DEFAULT 'local';");
    mysqli_query($connection, "ALTER TABLE visual_settings CHANGE `site_color` `site_color` VARCHAR(100) DEFAULT '#1abc9c';");
    mysqli_query($connection, "ALTER TABLE visual_settings CHANGE `site_block_color` `site_block_color` VARCHAR(100) DEFAULT '#161616 ';");
    mysqli_query($connection, "UPDATE visual_settings SET site_color='#1abc9c' WHERE id='1'");
    mysqli_query($connection, "UPDATE visual_settings SET site_block_color='#161616' WHERE id='1'");
    mysqli_query($connection, "UPDATE general_settings SET version='1.9' WHERE id='1'");
    mysqli_query($connection, "ALTER TABLE general_settings ADD COLUMN `allowed_file_extensions` TEXT DEFAULT 'zip';");

    mysqli_query($connection, "ALTER TABLE comments CHANGE `ip_address` `ip_address` VARCHAR(45);");
    mysqli_query($connection, "ALTER TABLE post_pageviews_month CHANGE `ip_address` `ip_address` VARCHAR(45);");
    mysqli_query($connection, "ALTER TABLE post_pageviews_week CHANGE `ip_address` `ip_address` VARCHAR(45);");

}

function add_new_translations($license_code, $purchase_code, $connection)
{
    $lang = array();
    $lang["confirm_user_email"] = "Confirm User Email";
    $lang["warning"] = "Warning";
    $lang["warning_server_time"] = "Scheduled Posts are published based on your server time. Your current server time:";
    $lang["gmail_warning"] = "To send e-mails with Gmail server, please read Email Settings section in our documentation.";
    $lang["album"] = "Album";
    $lang["albums"] = "Albums";
    $lang["gallery_albums"] = "Gallery Albums";
    $lang["add_album"] = "Add Album";
    $lang["album_name"] = "Album Name";
    $lang["msg_delete_album"] = "Please delete categories belonging to this album first!";
    $lang["confirm_album"] = "Are you sure you want to delete this album?";
    $lang["update_album"] = "Update Album";
    $lang["select_multiple_images"] = "You can select multiple images.";
    $lang["album_cover"] = "Album Cover";
    $lang["set_as_album_cover"] = "Set as Album Cover";
    $lang["show_email_on_profile"] = "Show Email on Profile Page";
    $lang["mail_library"] = "Mail Library";
    $lang["terms_conditions"] = "Terms & Conditions";
    $lang["terms_conditions_exp"] = "I have read and agree to the";
    $lang["forgot_password"] = "Forgot Password";
    $lang["email_reset_password"] = "Please click on the button below to reset your password.";
    $lang["reset_password_success"] = "We've sent an email for resetting your password to your email address. Please check your email for next steps.";
    $lang["new_password"] = "New Password";
    $lang["or_register_with_email"] = "Or register with email";
    $lang["or_login_with_email"] = "Or login with email";
    $lang["enter_email_address"] = "Enter your email address";
    $lang["enter_new_password"] = "Enter your new password";
    $lang["to"] = "To:";
    $lang["send_email_subscriber"] = "Send Email to Subscriber";
    $lang["post_comment"] = "Post Comment";
    $lang["load_more_comments"] = "Load More Comments";
    $lang["author"] = "Author";
    $lang["msg_cron_scheduled"] = "If you want to automatically publish scheduled posts, you should create a Cron Job function with this URL. For more information, please read the documentation.";
    $lang["confirm_messages"] = "Are you sure you want to delete selected messages?";
    $lang["sort_slider_posts"] = "Sort Slider Posts";
    $lang["sort_featured_posts"] = "Sort Featured Posts";
    $lang["by_date"] = "by Date";
    $lang["by_slider_order"] = "by Slider Order";
    $lang["by_featured_order"] = "by Featured Order";
    $lang["uploading"] = "Uploading...";
    $lang["vkontakte"] = "VKontakte";
    $lang["add_gallery"] = "Add Gallery";
    $lang["update_gallery"] = "Update Gallery";
    $lang["gallery_post_items"] = "Gallery Post Items";
    $lang["add_new_item"] = "Add New Item";
    $lang["save_and_continue"] = "Save and Continue";
    $lang["confirm_item"] = "Are you sure you want to delete this item?";
    $lang["update_gallery"] = "Update Gallery";
    $lang["edit_gallery_items"] = "Edit Gallery Items";
    $lang["edit_post_details"] = "Edit Post Details";
    $lang["gallery_post"] = "Gallery Post";
    $lang["next"] = "Next";
    $lang["previous"] = "Previous";
    $lang["gallery_post_exp"] = "A collection of images";
    $lang["ordered_list"] = "Ordered List";
    $lang["ordered_list_exp"] = "Add a list based article";
    $lang["add_article"] = "Add Article";
    $lang["update_article"] = "Update Article";
    $lang["update_audio"] = "Update Audio";
    $lang["add_ordered_list"] = "Add Ordered List";
    $lang["update_ordered_list"] = "Update Ordered List";
    $lang["ordered_list_items"] = "Ordered List Items";
    $lang["update_ordered_list"] = "Update Ordered List";
    $lang["edit_list_items"] = "Edit List Items";
    $lang["last_seen"] = "Last seen:";
    $lang["connect_with_facebook"] = "Connect with Facebook";
    $lang["connect_with_google"] = "Connect with Google";
    $lang["connect_with_vk"] = "Connect with VK";
    $lang["ip_address"] = "Ip Address";
    $lang["show_item_numbers"] = "Show Item Numbers in Post Details Page";
    $lang["secure_key"] = "Secure Key";
    $lang["embed_media"] = "Embed Media";
    $lang["add_iframe"] = "Add Iframe";
    $lang["text_editor_language"] = "Text Editor Language";
    $lang["upload_image"] = "Upload Image";
    $lang["drag_drop_files_here"] = "Drag and drop files here or";
    $lang["drag_drop_file_here"] = "Drag and drop file here or";
    $lang["browse_files"] = "Browse Files";
    $lang["txt_processing"] = "Processing...";
    $lang["file_upload"] = "File Upload";
    $lang["pageviews"] = "Pageviews";
    $lang["show_images_from_original_source"] = "Show Images from Original Source";
    $lang["download_images_my_server"] = "Download Images to My Server";
    $lang["maintenance_mode"] = "Maintenance Mode";
    $lang["post_options"] = "Post Options";
    $lang["route_settings"] = "Route Settings";
    $lang["route_settings_warning"] = "You cannot use special characters in routes. If your language contains special characters, please be careful when editing routes. If you enter an invalid route, you will not be able to access the related page.";
    $lang["general"] = "General";
    $lang["homepage"] = "Homepage";
    $lang["main_post_image"] = "Main post image";
    $lang["more_main_images"] = "More main images (slider will be active)";
    $lang["add_media"] = "Add Media";
    $lang["files"] = "Files";
    $lang["files_exp"] = "Downloadable additional files (.pdf, .docx, .zip etc..)";
    $lang["audios_exp"] = "Select your audios and create your playlist";
    $lang["msg_beforeunload"] = "You have unsaved changes! Are you sure you want to leave this page?";
    $lang["item_order"] = "Item Order";
    $lang["trivia_quiz"] = "Trivia Quiz";
    $lang["trivia_quiz_exp"] = "Quizzes with right and wrong answers";
    $lang["personality_quiz"] = "Personality Quiz";
    $lang["personality_quiz_exp"] = "Quizzes with custom results";
    $lang["add_quiz"] = "Add Quiz";
    $lang["add_trivia_quiz"] = "Add Trivia Quiz";
    $lang["add_personality_quiz"] = "Add Personality Quiz";
    $lang["update_trivia_quiz"] = "Update Trivia Quiz";
    $lang["update_personality_quiz"] = "Update Personality Quiz";
    $lang["questions"] = "Questions";
    $lang["confirm_question"] = "Are you sure you want to delete this question?";
    $lang["confirm_answer"] = "Are you sure you want to delete this answer?";
    $lang["confirm_result"] = "Are you sure you want to delete this result?";
    $lang["add_question"] = "Add Question";
    $lang["answer_text"] = "Answer Text";
    $lang["add_answer"] = "Add Answer";
    $lang["answers"] = "Answers";
    $lang["correct"] = "Correct";
    $lang["correct_answer"] = "Correct Answer";
    $lang["wrong_answer"] = "Wrong Answer";
    $lang["quiz_images"] = "Quiz Images";
    $lang["answer_format"] = "Answer Format";
    $lang["result"] = "Result";
    $lang["results"] = "Results";
    $lang["add_result"] = "Add Result";
    $lang["min"] = "Min";
    $lang["max"] = "Max";
    $lang["number_of_correct_answers"] = "Number of Correct Answers";
    $lang["number_of_correct_answers_range"] = "The range of correct answers to show this result";
    $lang["play_again"] = "Play Again";
    $lang["select_a_result"] = "Select a result";
    $lang["msg_added"] = "Item successfully added!";
    $lang["msg_deleted"] = "Item successfully deleted!";
    $lang["post_formats"] = "Post Formats";
    $lang["font_settings"] = "Font Settings";
    $lang["font_family"] = "Font Family";
    $lang["add_font"] = "Add Font";
    $lang["update_font"] = "Update Font";
    $lang["site_font"] = "Site Font";
    $lang["custom_css_codes"] = "Custom CSS Codes";
    $lang["custom_css_codes_exp"] = "These codes will be added to the header of the site.";
    $lang["custom_javascript_codes"] = "Custom JavaScript Codes";
    $lang["custom_javascript_codes_exp"] = "These codes will be added to the footer of the site.";
    $lang["adsense_activation_code"] = "AdSense Activation Code";
    $lang["comment_approval_system"] = "Comment Approval System";
    $lang["msg_comment_sent_successfully"] = "Your comment has been sent. It will be published after being reviewed by the site management.";
    $lang["msg_comment_approved"] = "Comment successfully approved!";
    $lang["pending_comments"] = "Pending Comments";
    $lang["approved_comments"] = "Approved Comments";
    $lang["recently_added_comments"] = "Recently added comments";
    $lang["recently_added_unapproved_comments"] = "Recently added unapproved comments";
    $lang["recently_added_contact_messages"] = "Recently added contact messages";
    $lang["recently_registered_users"] = "Recently registered users";
    $lang["audio_download_button"] = "Audio Download Button";
    $lang["updated"] = "Updated";
    $lang["themes"] = "Themes";
    $lang["activate"] = "Activate";
    $lang["activated"] = "Activated";
    $lang["dark_mode"] = "Dark Mode";
    $lang["ok"] = "OK";
    $lang["cancel"] = "Cancel";
    $lang["navigation_exp"] = "You can manage the navigation by dragging and dropping menu items.";
    $lang["nav_drag_warning"] = "You cannot drag a category below a page or a page below a category link!";
    $lang["article_post_exp"] = "An article with images and embed videos";
    $lang["video_post_exp"] = "Upload or embed videos";
    $lang["audio_post_exp"] = "Upload audios and create playlist";
    $lang["sorted_list"] = "Sorted List";
    $lang["add_sorted_list"] = "Add Sorted List";
    $lang["update_sorted_list"] = "Update Sorted List";
    $lang["sorted_list_items"] = "Sorted List Items";
    $lang["sorted_list_exp"] = "A list based article";
    $lang["invalid"] = "Invalid!";
    $lang["msg_delete_subpages"] = "Please delete subpages/sublinks first!";
    $lang["msg_rss_warning"] = "If you chose to download the images to your server, adding posts will take more time and will use more resources. If you see any problems, increase 'max_execution_time' and 'memory_limit' values from your server settings.";
    $lang["send_test_email"] = "Send Test Email";
    $lang["send_test_email_exp"] = "You can send a test mail to check if your mail server is working.";
    $lang["edit_translations"] = "Edit Translations";
    $lang["dashboard"] = "Dashboard";
    $lang["earnings"] = "Earnings";
    $lang["payouts"] = "Payouts";
    $lang["pageviews"] = "Pageviews";
    $lang["reward_system"] = "Reward System";
    $lang["reward_amount"] = "Reward Amount for 1000 Pageviews";
    $lang["currency_name"] = "Currency Name";
    $lang["currency_symbol"] = "Currency Symbol";
    $lang["currency_format"] = "Currency Format";
    $lang["currency"] = "Currency";
    $lang["user_id"] = "User Id";
    $lang["total_pageviews"] = "Total Pageviews";
    $lang["balance"] = "Balance";
    $lang["currency_symbol_format"] = "Currency Symbol Format";
    $lang["left"] = "Left";
    $lang["right"] = "Right";
    $lang["payouts"] = "Payouts";
    $lang["amount"] = "Amount";
    $lang["payout_method"] = "Payout Method";
    $lang["payout_methods"] = "Payout Methods";
    $lang["cookie_prefix"] = "Cookie Prefix";
    $lang["add_payout"] = "Add Payout";
    $lang["insufficient_balance"] = "Insufficient balance!";
    $lang["msg_payout_added"] = "Payout has been successfully added!";
    $lang["confirm_record"] = "Are you sure you want to delete this record?";
    $lang["paypal"] = "PayPal";
    $lang["iban"] = "IBAN";
    $lang["swift"] = "SWIFT";
    $lang["set_payout_account"] = "Set Payout Account";
    $lang["paypal_email_address"] = "PayPal Email Address";
    $lang["set_default_payment_account"] = "Set as Default Payment Account";
    $lang["full_name"] = "Full Name";
    $lang["bank_name"] = "Bank Name";
    $lang["iban_long"] = "International Bank Account Number";
    $lang["swift_iban"] = "Bank Account Number/IBAN";
    $lang["postcode"] = "Postcode";
    $lang["bank_account_holder_name"] = "Bank Account Holder's Name";
    $lang["bank_branch_country"] = "Bank Branch Country";
    $lang["bank_branch_city"] = "Bank Branch City";
    $lang["swift_code"] = "SWIFT Code";
    $lang["country"] = "Country";
    $lang["state"] = "State";
    $lang["city"] = "City";
    $lang["warning_default_payout_account"] = "Your earnings will be sent to your default payout account.";
    $lang["user_agent"] = "User-Agent";
    $lang["upload_csv_file"] = "Upload CSV File";
    $lang["completed"] = "Completed";
    $lang["help_documents"] = "Help Documents";
    $lang["help_documents_exp"] = "You can use these documents to generate your CSV file";
    $lang["category_ids_list"] = "Category Ids list";
    $lang["download_csv_template"] = "Download CSV Template";
    $lang["download_csv_example"] = "Download CSV Example";
    $lang["bulk_post_upload"] = "Bulk Post Upload";
    $lang["bulk_post_upload_exp"] = "You can add your posts with a CSV file from this section";
    $lang["importing_posts"] = "Importing posts...";
    $lang["documentation"] = "Documentation";
    $lang["field"] = "Field";
    $lang["data_type"] = "Data Type";
    $lang["required"] = "Required";
    $lang["optional"] = "Optional";
    $lang["show_user_email_profile"] = "Show User's Email on Profile";
    $lang["pwa_warning"] = "If you enable PWA option, read 'Progressive Web App (PWA)' section from our documentation to make the necessary settings.";
    $lang["email_status"] = "Email Status";
    $lang["enable_reward_system"] = "Enable Reward System";
    $lang["disable_reward_system"] = "Disable Reward System";
    $lang["delete_account"] = "Delete Account";
    $lang["delete_account_confirm"] = "Deleting your account is permanent and will remove all content including comments, avatars and profile settings. Are you sure you want to delete your account?";
    $lang["msg_wrong_password"] = "Wrong Password!";
    $lang["show_latest_posts_on_slider"] = "Show Latest Posts on Slider";
    $lang["show_latest_posts_on_featured"] = "Show Latest Posts on Featured Posts";
    $lang["allowed_file_extensions"] = "Allowed File Extensions";
    $lang["auto_post_deletion"] = "Auto Post Deletion";
    $lang["aws_base_url"] = "AWS Base URL";
    $lang["aws_key"] = "AWS Access Key";
    $lang["aws_secret"] = "AWS Secret Key";
    $lang["aws_storage"] = "AWS S3 Storage";
    $lang["backup"] = "Backup";
    $lang["bucket_name"] = "Bucket Name";
    $lang["delete_all_posts"] = "Delete All Posts";
    $lang["delete_only_rss_posts"] = "Delete only RSS Posts";
    $lang["download_database_backup"] = "Download Database Backup";
    $lang["encryption"] = "Encryption";
    $lang["export"] = "Export";
    $lang["file_extensions"] = "File Extensions";
    $lang["generated_sitemaps"] = "Generated Sitemaps";
    $lang["generate_keywords_from_title"] = "Generate Keywords from Title";
    $lang["generate_sitemap"] = "Generate Sitemap";
    $lang["horizontal"] = "Horizontal";
    $lang["import_language"] = "Import Language";
    $lang["invalid_file_type"] = "Invalid file type!";
    $lang["join_newsletter"] = "Join Our Newsletter";
    $lang["json_language_file"] = "JSON Language File";
    $lang["local_storage"] = "Local Storage";
    $lang["mail_is_being_sent"] = "Mail is being sent. Please do not close this page until the process is finished!";
    $lang["newsletter_desc"] = "Join our subscribers list to get the latest news, updates and special offers directly in your inbox";
    $lang["newsletter_email_error"] = "Select email addresses that you want to send mail!";
    $lang["newsletter_popup"] = "Newsletter Popup";
    $lang["newsletter_send_many_exp"] = "Some servers do not allow mass mailing. Therefore, instead of sending your mails to all subscribers at once, you can send them part by part (Example: 50 subscribers at once). If your mail server stops sending mail, the sending process will also stop.";
    $lang["no_thanks"] = "No, thanks";
    $lang["number_of_days"] = "Number of Days";
    $lang["number_of_days_exp"] = "If you add 30 here, the system will delete posts older than 30 days";
    $lang["redirect_rss_posts_to_original"] = "Redirect RSS Posts to the Original Site";
    $lang["region"] = "Region";
    $lang["reply_to"] = "Reply-To";
    $lang["set_as_default"] = "Set as Default";
    $lang["sitemap_generate_exp"] = "If your site has more than 50,000 links, the sitemap.xml file will be created in parts.";
    $lang["storage"] = "Storage";
    $lang["style"] = "Style";
    $lang["the_operation_completed"] = "The operation completed successfully!";
    $lang["translation"] = "Translation";
    $lang["vertical"] = "Vertical";
    //add new phrases
    $sql = "SELECT * FROM languages ORDER BY id";
    $result = mysqli_query($connection, $sql);
    while ($row = mysqli_fetch_array($result)) {
        if (!empty($lang)) {
            foreach ($lang as $key => $value) {
                $insert_new_translation = "INSERT INTO `language_translations` (`lang_id`, `label`, `translation`) 
                    VALUES (" . $row["id"] . ", '" . $key . "' , '" . $value . "')";
                mysqli_query($connection, $insert_new_translation);
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
    <title>Varient - Update Wizard</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,700" rel="stylesheet">
    <!-- Font-awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #444 !important;
            font-size: 14px;

            background: #007991; /* fallback for old browsers */
            background: -webkit-linear-gradient(to left, #007991, #6fe7c2); /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to left, #007991, #6fe7c2); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */

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
                    <h1>Varient</h1>
                    <p>Welcome to the Update Wizard</p>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="install-box">
                        <h2 class="title">Update from v1.5.x to v1.9</h2>
                        <br><br>
                        <div class="messages">
                            <?php if (!empty($error)) { ?>
                                <div class="alert alert-danger">
                                    <strong><?php echo $error; ?></strong>
                                </div>
                            <?php } ?>
                            <?php if (!empty($success)) { ?>
                                <div class="alert alert-success">
                                    <strong><?php echo $success; ?></strong>
                                    <style>.alert-info {
                                            display: none;
                                        }</style>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                        if (empty($success)):
                            if (empty($license_array) || empty($license_array["purchase_code"]) || empty($license_array["license_code"])): ?>
                                <div class="alert alert-info" role="alert">
                                    You can get your license code from our support desk: <a href="https://codingest.net/" target="_blank"><strong>https://codingest.net</strong></a>
                                </div>
                            <?php endif;
                        endif; ?>
                        <div class="step-contents">
                            <div class="tab-1">
                                <?php if (empty($success)): ?>
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                        <div class="tab-content">
                                            <div class="tab_1">
                                                <?php if (empty($license_array) || empty($license_array["purchase_code"]) || empty($license_array["license_code"])): ?>
                                                    <div class="form-group">
                                                        <label for="email">License Code</label>
                                                        <textarea name="license_code" class="form-control form-input" style="resize: vertical; min-height: 80px; height: 80px; line-height: 24px;padding: 10px;" placeholder="Enter License Code" required><?php echo $license_code; ?></textarea>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
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
