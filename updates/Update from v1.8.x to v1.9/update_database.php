<?php
define('BASEPATH', "/");
define('ENVIRONMENT', 'production');
require_once "application/config/database.php";
$license_code = '';
$purchase_code = '';
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
    update_18_to_19($license_code, $purchase_code, $connection);
    add_new_translations($license_code, $purchase_code, $connection);
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
                        <h2 class="title">Update from v1.8.x to v1.9</h2>
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
