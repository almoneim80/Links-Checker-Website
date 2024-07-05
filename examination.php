<?php
include 'connection.php';
session_start();
//include library simple_html_dom.php
include 'simple_html_dom.php';

// Function to check if a link is shortened
function isShortenedLink($url)
{
    $knownShorteners = [
        "http://bit.ly/", "https://bit.ly/",
        "http://t.co/", "https://t.co/",
        "bit.ly/", "t.co/"
    ]; // Add more known shorteners as needed

    // Add protocol prefix to known shorteners without a specified protocol
    $knownShorteners = array_map(function ($shortener) {
        return (strpos($shortener, 'http://') === false && strpos($shortener, 'https://') === false) ? 'http://' . $shortener : $shortener;
    }, $knownShorteners);

    // Append protocol prefix to input URL if it doesn't contain one
    if (strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
        $url = 'http://' . $url;
    }

    foreach ($knownShorteners as $shortener) {
        if (strpos($url, $shortener) !== false) {
            return true;
        }
    }
    return false;
}


function isInBlacklist($url)
{
    $blacklistFilePath = 'blacklist.txt';

    // Check if the blacklist file exists
    if (file_exists($blacklistFilePath)) {
        // Read the contents of the blacklist file into an array
        $blacklist = file($blacklistFilePath, FILE_IGNORE_NEW_LINES);

        // Check if the URL exists in the blacklist array
        if (in_array($url, $blacklist)) {
            return true; // URL is in the blacklist
        }
    }

    return false; // URL is not in the blacklist or the blacklist file doesn't exist
}


// Function to scan a link
function scanLink($url, $connection)
{
    // Initialize threats array
    $threats = [];

    // 1.
    // Sanitize user input (prevent code injection)
    $sanitizedUrl = filter_var($url, FILTER_SANITIZE_URL);  //filter to sanitize the input URL and prevent potential code injection.
    if (!$sanitizedUrl) {
        $threats[] = "The provided URL is empty or contains invalid characters.";
    }



    // 2. 
    // Check if the protocol is specified in the URL
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        // If the protocol is not specified, add a threat message and return
        $threats[] = "Link does not contain a protocol.";
    } else {
        // If the protocol is specified, check if it's HTTP
        if (strpos($url, "http://") === 0) {
            // If HTTP is used, add a warning to the threats
            $threats[] = "Unsafe protocol (HTTP) detected. ";
        }
    }


    // 3.
    // Check for shortened links
    if (isShortenedLink($url)) {
        $threats[] = "Shortened link";
    }



    // 4.    // Initialize cURL session.    cURL => (Client URL Library)
    $ch = curl_init();  //Initializes a cURL session and returns a cURL handle ($ch in this case) which is used in subsequent cURL functions

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);                      //Specifies the URL to fetch
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Set to `true` to return the transfer as a string instead of outputting it directly.
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  //Follows HTTP redirects
    curl_setopt($ch, CURLOPT_HEADER, false);                //Set to `false` to exclude the header from the output.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);   //Sets the maximum time in seconds that the request is allowed to take to connect to the server
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);                //Sets the maximum time in seconds that the request is allowed to execute.
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   // Set to `true` to verify the peer's SSL certificate.

    // Execute the request
    $response = curl_exec($ch);     // Executes the cURL session and retrieves the response from the server.

    // Check for cURL errors
    // if (curl_errno($ch)) {
    //     // Get the error message corresponding to the error code
    //     $errorMessage = curl_error($ch);

    //     // Determine the type of error based on the error code
    //     switch (curl_errno($ch)) {
    //         case CURLE_COULDNT_RESOLVE_HOST:
    //             $threats[] = " Could not resolve the host. This may be due to a DNS resolution issue.";
    //             break;
    //         case CURLE_OPERATION_TIMEOUTED:
    //             $threats[] = "URL operation timed out. Please try again later.";
    //             break;
    //         case CURLE_SSL_CONNECT_ERROR:
    //             $threats[] = "SSL connection error occurred. This may be due to SSL configuration issues.";
    //             break;
    //         case 60:
    //             $threats[] = "SSL verification failed. The SSL certificate of the remote server could not be verified.";
    //             break;
    //         case CURLE_SSL_CERTPROBLEM:
    //             $threats[] = " Problem with the SSL CA cert (path? access rights?).";
    //             break;
    //         case CURLE_SSL_CIPHER:
    //             $threats[] = "Couldn't use specified SSL cipher.";
    //             break;
    //         default:
    //             // For other cURL errors, provide a generic error message
    //             $threats[] = "URL request failed. " . $errorMessage;
    //             break;
    //     }
    // }


    // 5. 
    // Check local blacklist
    if (isInBlacklist($url)) {
        $threats[] = "The URL is in blacklisted, which means it is not safe.";
    }

    // 6.
    // Threat detection using regular expressions
    $malwarePatterns = [
        ["pattern" => "/(malware|exploit|crack)/i", "message" => "Malware or exploit detected"],
        ["pattern" => "/(download\s*this\s*file)/i", "message" => "Attempt to download suspicious file"],
        ["pattern" => "/\b(urgent|limited time|act now|don.t miss out)\b/i", "message" => "Urgency or scarcity manipulation"],
        ["pattern" => "/\b(bank|account|credit card|verify|update your information|login as)\s+(.+?)\b/i", "message" => "Phishing attempt related to banking or account information"],
        ["pattern" => "/\b(virus|trojan|exploit|hack|remote access|spyware|keylogger)\b/i", "message" => "Generic threat keywords detected"],
        ["pattern" => "/\\x[0-9a-fA-F]{2}/", "message" => "Hex encoding detected"],
    ];

    foreach ($malwarePatterns as $threat) {
        if (preg_match($threat['pattern'], $response)) {
            $threats[] = $threat['message'];
        }
    }


    // 7. 
    // $phishingPatterns = [
    //     ["pattern" => "/(phishing|fake\s*login)/i", "message" => "Phishing or fake login detected"],
    //     ["pattern" => "/(credit\s*card|bank\s*account)/i", "message" => "Credit card or bank account phishing attempt"],
    //     ["pattern" => "/\b(urgent|important|verify|confirm)\b/i", "message" => "Urgency or need for immediate action implied"],
    //     ["pattern" => "/\b(secure|official|authentic|legitimate)\b/i", "message" => "Attempt to appear legitimate or secure"],
    //     ["pattern" => "/\b(account\s*information|password\s*reset)\b/i", "message" => "Attempt to acquire account information or reset passwords"],
    //     ["pattern" => "/\b(update\s*your\s*information|confirm\s*your\s*identity)\b/i", "message" => "Attempt to confirm or update personal information"],
    //     ["pattern" => "/\b(log\s*in\s*to\s*your\s*account|access\s*your\s*profile)\b/i", "message" => "Attempt to gain access to user accounts"],
    //     ["pattern" => "/(http|https):\/\/[^\s]+/", "message" => "Direct URL used (potential for phishing)"],
    //     ["pattern" => "/\b(please\s*click\s*on|click\s*the\s*link)\b/i", "message" => "Request to click on links"],
    //     ["pattern" => "/\b(verify\s*your\s*email\s*address)\b/i", "message" => "Request to verify email address"],
    //     ["pattern" => "/\b(your\s*account\s*has\s*been\s*suspended)\b/i", "message" => "Notification of account suspension"],
    //     ["pattern" => "/\b(free\s*gift|claim\s*now)\b/i", "message" => "Promise of free gift or reward (common in phishing)"],
    //     ["pattern" => "/\b(irs\.gov|paypal\.com|amazon\.com)\b/i", "message" => "Impersonation of legitimate websites for phishing"],
    //     ["pattern" => "/\b(download|install|free software|click here)\b/i", "message" => "Prompt for potentially unsafe action"],
    //     ["pattern" => "/(ransomware|cryptojacking)/i", "message" => "Ransomware or cryptojacking threat detected"],
    //     ["pattern" => "/(backdoor|rootkit|payload)/i", "message" => "Backdoor or rootkit threat detected"],
    //     ["pattern" => "/\b(suspicious|unknown|untrusted)\b/i", "message" => "Indicates a potentially suspicious file or activity"],
    //     ["pattern" => "/\%[0-9a-fA-F]{2}/", "message" => "URL encoding detected"],
    // ];

    // foreach ($phishingPatterns as $pattern) {
    //     if (preg_match($pattern['pattern'], $response)) {
    //         $threats[] = $pattern['message'];
    //     }
    // }

    $checkQuery = "SELECT COUNT(*) FROM links WHERE link = :link";
    $checkStatement = $connection->prepare($checkQuery);
    $checkStatement->bindParam(':link', $_POST['link'], PDO::PARAM_STR);
    $checkStatement->execute();
    $linkExists = (bool) $checkStatement->fetchColumn();

    // If no threats detected, add a message indicating the link is safe
    if (empty($threats)) {
        $threats[] = "URL is safe 😀🎈. ";
        if (!$linkExists) {
            // Link does not exist, so proceed with insertion
            $query = "INSERT INTO links(link, is_safe) VALUES (:link, 1)";
            $statement = $connection->prepare($query);
            $statement->bindParam(':link', $_POST['link'], PDO::PARAM_STR);
            $statement->execute();
        }
    } else {
        if (!$linkExists) {
            // Link does not exist, so proceed with insertion
            $query = "INSERT INTO links(link, is_safe) VALUES (:link, 0)";
            $statement = $connection->prepare($query);
            $statement->bindParam(':link', $_POST['link'], PDO::PARAM_STR);
            $statement->execute();
        }
    }

    return $threats;
}
?>



<!doctype html>
<html lang="en">

<head>
    <?php include("head.php"); ?>
</head>

<body id="top">
    <main>
        <?php include("Nav.php"); ?>


        <section class="hero-section d-flex justify-content-center align-items-center" id="section_1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-12 mx-auto">
                        <h1 class="text-white text-center">Link Scanning</h1>
                        <h6 class="text-center">Service would involve scanning the provided URL for potential threats such as malware, phishing, or other security risks.</h6>

                        <form method="POST" class="custom-form mt-4 pt-2 mb-lg-0 mb-5" id="scan-form">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text link-icon bi bi-link" id="basic-addon1"></span>
                                <input name="link" type="text" class="form-control" id="keyword" placeholder="Paste the website link here ..." aria-label="Scan">
                                <button type="submit" class="form-control">Scan</button>
                            </div>
                        </form>

                        <div id="loading" style="display: none;" class='mt-4 alert alert-info'>
                            <h6 class="text-center">Scanning in progress... <i class="bi bi-hourglass-split"></i></h6>
                        </div>

                        <div>
                            <?php
                            // Check if the form is submitted
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                // Check if the link parameter is present
                                if (isset($_POST['link'])) {
                                    $link = $_POST['link'];
                                    $scanResult = scanLink($link, $connection);

                                    // Output the scan result
                                    echo "<div id='scan-result' class='mt-4 alert alert-info'>";
                                    echo "<h4>Examination results:</h4>";
                                    echo "<p style='overflow-wrap: break-word;'>Sent link: $link</p>";
                                    echo "<p>Scan time: " . date("Y-m-d H:i:s") . "</p>";
                                    echo "<p>Threats:</p>";
                                    echo "<ul>";

                                    // Check if $scanResult is an array
                                    if (is_array($scanResult)) {
                                        foreach ($scanResult as $threat) {
                                            echo "<li>"  . htmlentities($threat) . "</li>";
                                        }
                                    } else {
                                        // If $scanResult is not an array, output it as a single threat
                                        echo "<li>" . htmlentities($scanResult) . "</li>";
                                    }

                                    echo "</ul></div>";
                                }
                            }
                            ?>
                        </div>

                    </div>
                </div>
            </div>
        </section>

    </main>
    <?php include("footer.php"); ?>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.sticky.js"></script>
    <script src="js/custom.js"></script>
    <script>
        $(document).ready(function() {
            $('#scan-form').submit(function(e) {
                $('#loading').show();
            });
        });
    </script>
</body>

</html>