<?php
/*
|--------------------------------------------------------------------------
| SHUBHANKAR FASHION - SINGLE FILE CHATBOT
|--------------------------------------------------------------------------
| File: chatbot.php
|
| Usage in footer.php:
| <?php include 'chatbot.php'; ?>
|--------------------------------------------------------------------------
| UPDATED: Ab agar user login hai (session me user_id set hai), to
| chatbot uska naam dikhayega aur uski chat "user" table ke sath
| link karke DB me save karega.
|
| NOTE: Is file ko kaam karne ke liye "chatbot_messages" table me
| do naye columns add karne honge (ek baar phpMyAdmin/SQL me chala lein):
|
|   ALTER TABLE chatbot_messages
|       ADD COLUMN user_id INT NULL AFTER session_id,
|       ADD COLUMN user_name VARCHAR(255) NULL AFTER user_id;
|
| Aur ye maan kar chala hun ki aapke login system me login hone ke
| baad session me user ki id is naam se set hoti hai:
|
|       $_SESSION['user_id'] = $user['id'];
|
| Agar aapke login code me session ka naam alag hai (jaise
| $_SESSION['uid'] ya $_SESSION['login_id']), to neeche wale block
| me "user_id" ki jagah wahi naam likh dein.
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php';


/*
|--------------------------------------------------------------------------
| CURRENT LOGGED-IN USER (agar login hai)
|--------------------------------------------------------------------------
*/

$sh_user_id   = null;
$sh_user_name = null;

if (!empty($_SESSION['user_id'])) {

    $sh_user_id = (int) $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT full_name
        FROM user
        WHERE id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("i", $sh_user_id);

        $stmt->execute();

        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {

            $sh_user_name = $row['full_name'];

        }

        $stmt->close();

    }

}


/*
|--------------------------------------------------------------------------
| AJAX REQUEST HANDLER
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json; charset=UTF-8');

    $action = $_POST['action'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | GET CHATBOT OPTIONS
    |--------------------------------------------------------------------------
    */

    if ($action === 'get_options') {

        $options = [];

        $sql = "
            SELECT
                id,
                title,
                message,
                link,
                icon
            FROM chatbot_options
            WHERE status = 1
            ORDER BY sort_order ASC, id ASC
        ";

        $result = $conn->query($sql);

        if ($result) {

            while ($row = $result->fetch_assoc()) {

                $options[] = $row;

            }

        }

        echo json_encode([
            'success' => true,
            'options' => $options
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | SEND CHAT MESSAGE
    |--------------------------------------------------------------------------
    */

    if ($action === 'send_message') {

        $message = trim($_POST['message'] ?? '');

        $session_id = trim($_POST['session_id'] ?? '');


        /*
        |--------------------------------------------------------------------------
        | CREATE SESSION
        |--------------------------------------------------------------------------
        */

        if ($session_id === '') {

            $session_id =
                'sh_' .
                bin2hex(random_bytes(12));

        }


        /*
        |--------------------------------------------------------------------------
        | EMPTY MESSAGE
        |--------------------------------------------------------------------------
        */

        if ($message === '') {

            echo json_encode([
                'success' => false,
                'message' => 'Please enter a message.'
            ]);

            exit;
        }


        /*
        |--------------------------------------------------------------------------
        | CLEAN MESSAGE
        |--------------------------------------------------------------------------
        */

        $lower = strtolower($message);


        /*
        |--------------------------------------------------------------------------
        | DEFAULT RESPONSE
        |--------------------------------------------------------------------------
        */

        $reply =
            "I'm here to help you with Shubhankar Fashion. " .
            "Please select an option below or tell me what you're looking for.";

        $link = null;


        /*
        |--------------------------------------------------------------------------
        | GREETING
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/\b(hi|hello|hey|hii|helo|namaste)\b/i',
                $lower
            )
        ) {

            $reply =
                ($sh_user_name ? "Hello, " . $sh_user_name . "! 👋 " : "Hello! 👋 ") .
                "Welcome to Shubhankar Fashion. " .
                "How can I help you today?";

        }


        /*
        |--------------------------------------------------------------------------
        | SHOP / PRODUCT
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'shop') !== false ||
            strpos($lower, 'product') !== false ||
            strpos($lower, 'buy') !== false ||
            strpos($lower, 'clothes') !== false ||
            strpos($lower, 'dress') !== false
        ) {

            $reply =
                "Sure! 🛍️ Explore our latest fashion products here.";

            $link = "shop";

        }


        /*
        |--------------------------------------------------------------------------
        | CATEGORY
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'categor') !== false ||
            strpos($lower, 'collection') !== false
        ) {

            $reply =
                "You can explore all our fashion categories here.";

            $link = "categories";

        }


        /*
        |--------------------------------------------------------------------------
        | NEW ARRIVAL
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'new arrival') !== false ||
            strpos($lower, 'new collection') !== false ||
            strpos($lower, 'latest') !== false
        ) {

            $reply =
                "✨ Check out our latest fashion arrivals.";

            $link = "shop";

        }


        /*
        |--------------------------------------------------------------------------
        | SIZE
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'size') !== false ||
            strpos($lower, 'fit') !== false ||
            strpos($lower, 'measurement') !== false
        ) {

            $reply =
                "📏 No problem! You can check our Size Guide " .
                "to find the right fit.";

            $link = "shop";

        }


        /*
        |--------------------------------------------------------------------------
        | CART
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'cart') !== false
        ) {

            $reply =
                "👤 You can view and manage your profile from here.";

            $link = "my-profile";

        }


        /*
        |--------------------------------------------------------------------------
        | WISHLIST
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'wishlist') !== false ||
            strpos($lower, 'wish list') !== false ||
            strpos($lower, 'saved') !== false
        ) {

            $reply =
                "❤️ Here you can view all your saved products.";

            $link = "wishlist";

        }


        /*
        |--------------------------------------------------------------------------
        | ORDER / TRACK
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'order') !== false ||
            strpos($lower, 'track') !== false ||
            strpos($lower, 'delivery status') !== false
        ) {

            $reply =
                "🚚 You can check your order status here.";

            $link = "track-order";

        }


        /*
        |--------------------------------------------------------------------------
        | RETURN / EXCHANGE
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'return') !== false ||
            strpos($lower, 'exchange') !== false ||
            strpos($lower, 'refund') !== false
        ) {

            $reply =
                "↩️ You can check our Return & Exchange Policy here.";

            $link = "return-policy";

        }


        /*
        |--------------------------------------------------------------------------
        | SHIPPING
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'shipping') !== false ||
            strpos($lower, 'delivery') !== false ||
            strpos($lower, 'deliver') !== false
        ) {

            $reply =
                "🚚 You can find complete shipping information here.";

            $link = "shipping-policy";

        }


        /*
        |--------------------------------------------------------------------------
        | FAQ
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'faq') !== false ||
            strpos($lower, 'question') !== false
        ) {

            $reply =
                "❓ You can find answers to common questions here.";

            $link = "faq";

        }


        /*
        |--------------------------------------------------------------------------
        | CONTACT
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'contact') !== false ||
            strpos($lower, 'support') !== false ||
            strpos($lower, 'help') !== false
        ) {

            $reply =
                "💬 Our support team will be happy to help you.";

            $link = "contact";

        }


        /*
        |--------------------------------------------------------------------------
        | PRICE
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($lower, 'price') !== false ||
            strpos($lower, 'cost') !== false ||
            strpos($lower, '₹') !== false ||
            strpos($lower, 'rupee') !== false
        ) {

            $reply =
                "You can browse our products and check the latest prices here.";

            $link = "shop";

        }


        /*
        |--------------------------------------------------------------------------
        | SAVE CHAT TO DATABASE (session ke sath user_id/user_name bhi)
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            INSERT INTO chatbot_messages
            (
                session_id,
                user_id,
                user_name,
                user_message,
                bot_reply
            )
            VALUES (?, ?, ?, ?, ?)
        ");


        if ($stmt) {

            $stmt->bind_param(
                "sisss",
                $session_id,
                $sh_user_id,
                $sh_user_name,
                $message,
                $reply
            );

            $stmt->execute();

            $stmt->close();

        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        echo json_encode([
            'success' => true,
            'reply' => $reply,
            'link' => $link,
            'session_id' => $session_id
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | INVALID ACTION
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        'success' => false,
        'message' => 'Invalid chatbot action.'
    ]);

    exit;
}

?>


<!-- =========================================================
     SHUBHANKAR CHATBOT UI
========================================================= -->

<div id="shChatbot" class="sh-chatbot">

    <!-- HEADER -->
    <div class="sh-chatbot-header">

        <button
            type="button"
            id="shChatbotClose"
            class="sh-chatbot-close-mobile"
            aria-label="Close chat"
        >
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="sh-chatbot-avatar">
            <i class="fa-solid fa-comments"></i>
        </div>

        <div class="sh-chatbot-header-text">

            <div class="sh-chatbot-title">
                Shubhankar Assistant
            </div>

            <div class="sh-chatbot-status">
                <span class="sh-online-dot"></span>
                <?php if ($sh_user_name): ?>
                    Chatting as <?php echo htmlspecialchars($sh_user_name); ?>
                <?php else: ?>
                    Online • We're here to help
                <?php endif; ?>
            </div>

        </div>

    </div>


    <!-- BODY -->
    <div
        id="shChatbotBody"
        class="sh-chatbot-body"
    >

        <!-- WELCOME MESSAGE -->

        <div class="sh-bot-row">

            <div class="sh-bot-message">

                <div class="sh-message-title">
                    <?php if ($sh_user_name): ?>
                        Hello, <?php echo htmlspecialchars($sh_user_name); ?>! 👋
                    <?php else: ?>
                        Hello! 👋
                    <?php endif; ?>
                </div>

                Welcome to Shubhankar Fashion.

                <br><br>

                I can help you find products,
                categories, size information,
                orders, wishlist and more.

            </div>

        </div>


        <!-- OPTIONS -->

        <div
            id="shChatbotOptions"
            class="sh-chatbot-options"
        >

            <div class="sh-chatbot-loading">
                Loading options...
            </div>

        </div>

    </div>


    <!-- FOOTER / INPUT -->

    <div class="sh-chatbot-footer">

        <form
            id="shChatbotForm"
            class="sh-chatbot-form"
        >

            <input
                type="text"
                id="shChatbotInput"
                class="sh-chatbot-input"
                placeholder="Ask me anything..."
                autocomplete="off"
            >

            <button
                type="submit"
                class="sh-chatbot-send"
                aria-label="Send message"
            >

                <i class="fa-solid fa-paper-plane"></i>

            </button>

        </form>

    </div>

</div>


<!-- =========================================================
     BACKDROP (mobile only)
========================================================= -->

<div id="shChatbotBackdrop" class="sh-chatbot-backdrop"></div>


<!-- =========================================================
     FLOATING CHAT BUTTON
========================================================= -->

<button
    type="button"
    id="shChatbotButton"
    class="sh-chatbot-button"
    aria-label="Open Chatbot"
>

    <span class="sh-chat-open-icon">
        <i class="fa-solid fa-comments"></i>
    </span>

    <span class="sh-chat-close-icon">
        <i class="fa-solid fa-xmark"></i>
    </span>

</button>


<style>

/* =========================================================
   SHUBHANKAR CHATBOT
   Themed with the site's own design tokens (see style.css).
   Falls back to hard-coded equivalents if those tokens
   aren't defined on the page the widget is included on.
========================================================= */

.sh-chatbot,
.sh-chatbot *,
.sh-chatbot-button,
.sh-chatbot-button *,
.sh-chatbot-backdrop {
    box-sizing: border-box;
}

.sh-chatbot{
    --sh-primary:   var(--primary, #00005f);
    --sh-primary-d: var(--primary-d, #000047);
    --sh-accent:    var(--accent, #C8973A);
    --sh-bg:        var(--bg, #FAFAF7);
    --sh-surface:   var(--surface, #FFFFFF);
    --sh-surface-2: var(--surface-2, #F2F0E9);
    --sh-text:      var(--text, #20241F);
    --sh-muted:     var(--muted, #6B6F66);
    --sh-border:    var(--border, #E7E4DA);
    --sh-font-display: var(--font-display, 'Fraunces', Georgia, serif);
    --sh-font-body: var(--font-body, 'Inter', -apple-system, Segoe UI, Roboto, sans-serif);
}


/* =========================================================
   FLOATING BUTTON
========================================================= */

.sh-chatbot-button {

    position: fixed;

    right: max(20px, env(safe-area-inset-right));
    bottom: max(20px, env(safe-area-inset-bottom, 0px) + 16px);

    width: 58px;
    height: 58px;

    border: 0;
    border-radius: 50%;

    background: var(--primary, #00005f);

    color: #ffffff;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 22px;

    cursor: pointer;

    z-index: 999999;

    box-shadow:
        0 8px 26px rgba(0,0,95,.28);

    transition:
        transform .25s ease,
        background .25s ease;

}


.sh-chatbot-button:hover {

    transform: translateY(-4px);

    background: var(--primary-d, #000047);

}


.sh-chat-close-icon {

    display: none;

}


.sh-chatbot-button.active
.sh-chat-open-icon {

    display: none;

}


.sh-chatbot-button.active
.sh-chat-close-icon {

    display: block;

}


/* =========================================================
   BACKDROP — dims the page behind the chat on small screens
========================================================= */

.sh-chatbot-backdrop{
    position: fixed;
    inset: 0;
    background: rgba(32,36,31,.35);
    z-index: 999997;
    opacity: 0;
    visibility: hidden;
    transition: opacity .25s ease;
}

.sh-chatbot-backdrop.active{
    opacity: 1;
    visibility: visible;
}

@media (min-width: 577px){
    .sh-chatbot-backdrop{ display: none; }
}


/* =========================================================
   CHAT WINDOW
   Fluid sizing: scales with viewport, capped to a sensible
   desktop max, and goes near-fullscreen on small phones.
========================================================= */

.sh-chatbot {

    position: fixed;

    right: max(20px, env(safe-area-inset-right));
    bottom: calc(90px + env(safe-area-inset-bottom, 0px));

    width: min(380px, calc(100vw - 40px));
    height: min(570px, calc(100dvh - 130px));

    background: var(--sh-surface);

    border-radius: 18px;

    overflow: hidden;

    z-index: 999998;

    display: flex;

    flex-direction: column;

    box-shadow:
        0 20px 60px rgba(32,36,31,.20);

    border: 1px solid var(--sh-border);

    opacity: 0;

    visibility: hidden;

    transform:
        translateY(20px)
        scale(.97);

    transition:
        all .3s ease;

    font-family: var(--sh-font-body);

}


.sh-chatbot.active {

    opacity: 1;

    visibility: visible;

    transform:
        translateY(0)
        scale(1);

}


/* =========================================================
   HEADER
========================================================= */

.sh-chatbot-header {

    flex-shrink: 0;

    padding: 16px 18px;

    background: var(--sh-primary);

    color: #ffffff;

    display: flex;

    align-items: center;

    gap: 12px;

}


.sh-chatbot-close-mobile{
    display: none;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    border: 0;
    background: transparent;
    color: #ffffff;
    font-size: 16px;
    align-items: center;
    justify-content: center;
    margin-left: -4px;
    margin-right: 2px;
    border-radius: 50%;
}

.sh-chatbot-close-mobile:hover{
    background: rgba(255,255,255,.12);
}


.sh-chatbot-avatar {

    width: 42px;
    height: 42px;

    flex-shrink: 0;

    border-radius: 50%;

    background: #ffffff;

    color: var(--sh-primary);

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 18px;

}


.sh-chatbot-header-text {

    min-width: 0;

}


.sh-chatbot-title {

    font-family: var(--sh-font-display);
    font-size: 16px;

    line-height: 1.3;

    font-weight: 600;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

}


.sh-chatbot-status {

    display: flex;

    align-items: center;

    gap: 5px;

    margin-top: 3px;

    font-size: 11px;

    opacity: .85;

}


.sh-online-dot {

    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: #35d06f;

    display: inline-block;

    flex-shrink: 0;

}


/* =========================================================
   BODY
========================================================= */

.sh-chatbot-body {

    flex: 1;

    overflow-y: auto;

    -webkit-overflow-scrolling: touch;

    padding: 16px;

    background: var(--sh-bg);

    scroll-behavior: smooth;

}


.sh-chatbot-body::-webkit-scrollbar {

    width: 5px;

}


.sh-chatbot-body::-webkit-scrollbar-thumb {

    background: var(--sh-border);

    border-radius: 10px;

}


/* =========================================================
   BOT MESSAGE
========================================================= */

.sh-bot-row {

    display: flex;

    justify-content: flex-start;

    margin-bottom: 14px;

}


.sh-bot-message {

    max-width: 88%;

    padding: 12px 14px;

    border-radius: 4px 14px 14px 14px;

    background: var(--sh-surface);

    color: var(--sh-text);

    font-size: 13px;

    line-height: 1.55;

    box-shadow:
        0 2px 10px rgba(32,36,31,.06);

    border: 1px solid var(--sh-border);

    word-break: break-word;

}


.sh-message-title {

    font-family: var(--sh-font-display);
    font-size: 14px;

    font-weight: 600;

    margin-bottom: 3px;

}


/* =========================================================
   USER MESSAGE
========================================================= */

.sh-user-row {

    display: flex;

    justify-content: flex-end;

    margin: 12px 0;

}


.sh-user-message {

    max-width: 80%;

    padding: 11px 14px;

    background: var(--sh-primary);

    color: #ffffff;

    border-radius:
        14px 4px 14px 14px;

    font-size: 13px;

    line-height: 1.5;

    word-break: break-word;

}


/* =========================================================
   OPTIONS
========================================================= */

.sh-chatbot-options {

    display: flex;

    flex-wrap: wrap;

    gap: 8px;

}


.sh-chatbot-option {

    border: 1px solid var(--sh-border);

    background: var(--sh-surface);

    color: var(--sh-text);

    border-radius: 25px;

    padding: 9px 12px;

    font-size: 12px;

    line-height: 1.2;

    cursor: pointer;

    transition: all .2s ease;

}


.sh-chatbot-option:hover {

    background: var(--sh-primary);

    color: #ffffff;

    border-color: var(--sh-primary);

    transform: translateY(-1px);

}


.sh-chatbot-option i {

    margin-right: 5px;

    color: var(--sh-accent);

}


.sh-chatbot-option:hover i{
    color: #ffffff;
}


/* =========================================================
   LINK
========================================================= */

.sh-chatbot-link {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    margin-top: 9px;

    padding: 8px 13px;

    background: var(--sh-primary);

    color: #ffffff !important;

    text-decoration: none !important;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 600;

}


.sh-chatbot-link:hover {

    background: var(--sh-primary-d);

    color: #ffffff !important;

}


/* =========================================================
   LOADING
========================================================= */

.sh-chatbot-loading {

    color: var(--sh-muted);

    font-size: 12px;

}


/* =========================================================
   TYPING
========================================================= */

.sh-typing {

    display: flex;

    align-items: center;

    gap: 4px;

    padding: 10px 13px;

    background: var(--sh-surface);

    border: 1px solid var(--sh-border);

    border-radius: 4px 14px 14px 14px;

    width: fit-content;

}


.sh-typing span {

    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: var(--sh-muted);

    animation:
        shTyping 1.2s infinite;

}


.sh-typing span:nth-child(2) {

    animation-delay: .2s;

}


.sh-typing span:nth-child(3) {

    animation-delay: .4s;

}


@keyframes shTyping {

    0%,
    60%,
    100% {

        transform:
            translateY(0);

    }

    30% {

        transform:
            translateY(-5px);

    }

}


/* =========================================================
   CHAT FOOTER
========================================================= */

.sh-chatbot-footer {

    flex-shrink: 0;

    padding: 10px 11px calc(10px + env(safe-area-inset-bottom, 0px));

    background: var(--sh-surface);

    border-top: 1px solid var(--sh-border);

}


.sh-chatbot-form {

    display: flex;

    align-items: center;

    gap: 8px;

}


.sh-chatbot-input {

    flex: 1;

    min-width: 0;

    height: 43px;

    padding: 0 15px;

    border:
        1px solid var(--sh-border);

    border-radius: 25px;

    outline: none;

    background: var(--sh-surface);

    color: var(--sh-text);

    /* 16px prevents iOS Safari from auto-zooming on focus */
    font-size: 16px;

}


.sh-chatbot-input:focus {

    border-color: var(--sh-primary);

    box-shadow: 0 0 0 3px rgba(0,0,95,.10);

}


.sh-chatbot-input::placeholder{
    font-size: 13px;
    color: var(--sh-muted);
}


.sh-chatbot-send {

    width: 43px;
    height: 43px;

    flex-shrink: 0;

    border: 0;

    border-radius: 50%;

    background: var(--sh-primary);

    color: #ffffff;

    cursor: pointer;

    display: flex;

    align-items: center;
    justify-content: center;

    transition: background .2s ease, transform .1s ease;

}


.sh-chatbot-send:hover {

    background: var(--sh-primary-d);

}


.sh-chatbot-send:active{
    transform: scale(.94);
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 768px){

    .sh-chatbot{
        right: max(16px, env(safe-area-inset-right));
        bottom: calc(84px + env(safe-area-inset-bottom, 0px));
        width: min(360px, calc(100vw - 32px));
        height: min(560px, calc(100dvh - 120px));
    }

}


/* =========================================================
   MOBILE — near-fullscreen sheet
========================================================= */

@media (max-width: 576px) {

    .sh-chatbot-close-mobile{
        display: flex;
    }

    .sh-chatbot {

        left: 0;
        right: 0;
        bottom: 0;

        width: 100%;

        height: 100dvh;
        height: 100vh; /* fallback for browsers without dvh */

        max-height: none;

        border-radius: 0;

        border: 0;

        transform: translateY(16px);

    }

    .sh-chatbot.active{
        transform: translateY(0);
    }

    .sh-chatbot-button {

        right: max(16px, env(safe-area-inset-right));

        bottom: max(16px, env(safe-area-inset-bottom, 0px) + 12px);

        width: 54px;
        height: 54px;

        font-size: 20px;

    }


    .sh-chatbot-header {

        padding: max(14px, env(safe-area-inset-top, 0px)) 14px 14px;

    }


    .sh-chatbot-body {

        padding: 14px;

    }


    .sh-bot-message,
    .sh-user-message{
        max-width: 92%;
    }


    .sh-chatbot-option {

        padding: 9px 11px;

        font-size: 12px;

    }


    .sh-chatbot-input {

        height: 44px;

    }


    .sh-chatbot-send {

        width: 44px;
        height: 44px;

    }

}


/* =========================================================
   VERY SMALL / NARROW DEVICES
========================================================= */

@media (max-width: 360px) {

    .sh-chatbot-header{
        padding: max(12px, env(safe-area-inset-top, 0px)) 12px 12px;
        gap: 10px;
    }

    .sh-chatbot-avatar{
        width: 38px;
        height: 38px;
        font-size: 16px;
    }

    .sh-chatbot-title{
        font-size: 15px;
    }

    .sh-chatbot-body{
        padding: 12px;
    }

    .sh-chatbot-button {

        right: 12px;
        bottom: max(12px, env(safe-area-inset-bottom, 0px) + 10px);

        width: 50px;
        height: 50px;

        font-size: 19px;

    }

}


/* =========================================================
   LANDSCAPE PHONES — keep the sheet usable when short/wide
========================================================= */

@media (max-width: 900px) and (max-height: 480px) and (orientation: landscape){

    .sh-chatbot{
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        width: 100%;
        height: 100dvh;
        height: 100vh;
        border-radius: 0;
        border: 0;
    }

    .sh-chatbot-header{
        padding: 10px 14px;
    }

    .sh-chatbot-body{
        padding: 10px 14px;
    }

}


/* =========================================================
   REDUCED MOTION
========================================================= */

@media (prefers-reduced-motion: reduce){

    .sh-chatbot,
    .sh-chatbot-button,
    .sh-chatbot-backdrop,
    .sh-chatbot-send{
        transition: none !important;
    }

    .sh-typing span{
        animation: none !important;
    }

}

</style>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


    const chatbot =
        document.getElementById(
            "shChatbot"
        );


    const button =
        document.getElementById(
            "shChatbotButton"
        );


    const backdrop =
        document.getElementById(
            "shChatbotBackdrop"
        );


    const closeMobileBtn =
        document.getElementById(
            "shChatbotClose"
        );


    const body =
        document.getElementById(
            "shChatbotBody"
        );


    const options =
        document.getElementById(
            "shChatbotOptions"
        );


    const form =
        document.getElementById(
            "shChatbotForm"
        );


    const input =
        document.getElementById(
            "shChatbotInput"
        );


    /*
    |--------------------------------------------------------------------------
    | CHATBOT URL
    |--------------------------------------------------------------------------
    */

    const chatbotURL =
        "chatbot.php";


    /*
    |--------------------------------------------------------------------------
    | SESSION
    |--------------------------------------------------------------------------
    */

    let sessionId =
        localStorage.getItem(
            "sh_chatbot_session"
        );


    if (!sessionId) {

        sessionId =
            "sh_" +
            Date.now() +
            "_" +
            Math.random()
                .toString(36)
                .substring(2, 10);

        localStorage.setItem(
            "sh_chatbot_session",
            sessionId
        );

    }


    /*
    |--------------------------------------------------------------------------
    | OPEN / CLOSE
    |--------------------------------------------------------------------------
    */

    function isMobileView() {

        return window.matchMedia(
            "(max-width: 576px)"
        ).matches;

    }


    function openChat() {

        chatbot.classList.add("active");
        button.classList.add("active");
        backdrop.classList.add("active");

        if (isMobileView()) {
            document.body.style.overflow = "hidden";
        }

        setTimeout(
            function () {
                input.focus({ preventScroll: true });
            },
            300
        );

    }


    function closeChat() {

        chatbot.classList.remove("active");
        button.classList.remove("active");
        backdrop.classList.remove("active");

        document.body.style.overflow = "";

    }


    button.addEventListener(
        "click",
        function () {

            if (chatbot.classList.contains("active")) {
                closeChat();
            } else {
                openChat();
            }

        }
    );


    closeMobileBtn.addEventListener(
        "click",
        closeChat
    );


    backdrop.addEventListener(
        "click",
        closeChat
    );


    window.addEventListener(
        "resize",
        function () {

            if (
                !isMobileView() &&
                document.body.style.overflow === "hidden"
            ) {
                document.body.style.overflow = "";
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | LOAD OPTIONS
    |--------------------------------------------------------------------------
    */

    function loadOptions() {

        const data =
            new FormData();

        data.append(
            "action",
            "get_options"
        );


        fetch(
            chatbotURL,
            {
                method: "POST",
                body: data
            }
        )

        .then(
            response =>
                response.json()
        )

        .then(
            result => {

                if (
                    !result.success
                ) {

                    options.innerHTML =
                        "";

                    return;

                }


                options.innerHTML =
                    "";


                result.options.forEach(
                    function (item) {

                        const btn =
                            document
                                .createElement(
                                    "button"
                                );


                        btn.type =
                            "button";


                        btn.className =
                            "sh-chatbot-option";


                        const icon =
                            item.icon ||
                            "fa-circle";


                        btn.innerHTML =
                            '<i class="fa-solid ' +
                            escapeHTML(icon) +
                            '"></i>' +
                            escapeHTML(
                                item.title
                            );


                        btn.addEventListener(
                            "click",
                            function () {

                                addUserMessage(
                                    item.title
                                );


                                showTyping();


                                setTimeout(
                                    function () {

                                        hideTyping();


                                        addBotMessage(
                                            item.message,
                                            item.link
                                        );

                                    },
                                    450
                                );

                            }
                        );


                        options.appendChild(
                            btn
                        );

                    }
                );

            }
        )

        .catch(
            function (error) {

                console.error(
                    "Chatbot:",
                    error
                );

                options.innerHTML =
                    '<div class="sh-chatbot-loading">' +
                    'Unable to load options.' +
                    '</div>';

            }
        );

    }


    loadOptions();


    /*
    |--------------------------------------------------------------------------
    | SEND MESSAGE
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const message =
                input.value.trim();


            if (!message) {

                return;

            }


            addUserMessage(
                message
            );


            input.value =
                "";


            showTyping();


            const data =
                new FormData();


            data.append(
                "action",
                "send_message"
            );


            data.append(
                "message",
                message
            );


            data.append(
                "session_id",
                sessionId
            );


            fetch(
                chatbotURL,
                {
                    method: "POST",
                    body: data
                }
            )

            .then(
                response =>
                    response.json()
            )

            .then(
                result => {

                    hideTyping();


                    if (
                        !result.success
                    ) {

                        addBotMessage(
                            "Sorry, something went wrong. Please try again."
                        );

                        return;

                    }


                    if (
                        result.session_id
                    ) {

                        sessionId =
                            result.session_id;


                        localStorage.setItem(
                            "sh_chatbot_session",
                            sessionId
                        );

                    }


                    addBotMessage(
                        result.reply,
                        result.link
                    );

                }
            )

            .catch(
                function (error) {

                    hideTyping();


                    addBotMessage(
                        "Sorry, I couldn't process your request right now."
                    );


                    console.error(
                        "Chatbot:",
                        error
                    );

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | USER MESSAGE
    |--------------------------------------------------------------------------
    */

    function addUserMessage(
        message
    ) {

        const row =
            document.createElement(
                "div"
            );


        row.className =
            "sh-user-row";


        const msg =
            document.createElement(
                "div"
            );


        msg.className =
            "sh-user-message";


        msg.textContent =
            message;


        row.appendChild(
            msg
        );


        body.appendChild(
            row
        );


        scrollChat();

    }


    /*
    |--------------------------------------------------------------------------
    | BOT MESSAGE
    |--------------------------------------------------------------------------
    */

    function addBotMessage(
        message,
        link = null
    ) {

        const row =
            document.createElement(
                "div"
            );


        row.className =
            "sh-bot-row";


        const msg =
            document.createElement(
                "div"
            );


        msg.className =
            "sh-bot-message";


        msg.textContent =
            message;


        if (link) {

            const br =
                document.createElement(
                    "br"
                );


            const anchor =
                document.createElement(
                    "a"
                );


            anchor.href =
                link;


            anchor.className =
                "sh-chatbot-link";


            anchor.innerHTML =
                '<i class="fa-solid fa-arrow-right"></i> ' +
                'Open Page';


            msg.appendChild(
                br
            );


            msg.appendChild(
                anchor
            );

        }


        row.appendChild(
            msg
        );


        body.appendChild(
            row
        );


        scrollChat();

    }


    /*
    |--------------------------------------------------------------------------
    | TYPING
    |--------------------------------------------------------------------------
    */

    function showTyping() {

        if (
            document.getElementById(
                "shTyping"
            )
        ) {

            return;

        }


        const row =
            document.createElement(
                "div"
            );


        row.id =
            "shTyping";


        row.className =
            "sh-bot-row";


        row.innerHTML =
            '<div class="sh-typing">' +
            '<span></span>' +
            '<span></span>' +
            '<span></span>' +
            '</div>';


        body.appendChild(
            row
        );


        scrollChat();

    }


    function hideTyping() {

        const typing =
            document.getElementById(
                "shTyping"
            );


        if (typing) {

            typing.remove();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | SCROLL
    |--------------------------------------------------------------------------
    */

    function scrollChat() {

        body.scrollTop =
            body.scrollHeight;

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHTML(
        value
    ) {

        const div =
            document.createElement(
                "div"
            );


        div.textContent =
            value;


        return div.innerHTML;

    }


});

</script>