<?php
function ade_set_cookie($expire) {
    $userid = false;

    if (array_key_exists('UNIQUE_ID', $_SERVER) && !empty($_SERVER['UNIQUE_ID'])) : // apache
        $userid = $_SERVER['UNIQUE_ID'];
    elseif (array_key_exists('X-Request-ID', $_SERVER) && !empty($_SERVER['X-Request-ID'])) : // nginx
        /**
         * Header must be set in site's Nginx conf
         *
         * server {
         *    listen 80 default_server;
         *    ...
         *    location ~ '\.php$' {
         *        fastcgi_param      X-Request-ID $request_id;
         *    ...
         * }
         */
        $userid = $_SERVER['X-Request-ID'];
    endif;

    setcookie('unique_userid', $userid, $expire, '/', $_SERVER['SERVER_NAME']);

    if ($userid !== false) :
        // store the user id in a cookie
        setcookie('unique_userid', $userid, $expire, '/', $_SERVER['SERVER_NAME']);

        // store the user id expire time in a cookie
        setcookie('userid_expire', $expire, $expire, '/', $_SERVER['SERVER_NAME']);
    endif;

    return $userid;
}

function ade_get_cookie() {
    $userid = false;
    $expire = time() + (60 * 60 * 6); // cookie expire time, now + 6 hours

    if (isset($_COOKIE['unique_userid']) && isset($_COOKIE['userid_expire'])) :
        $userid_expire = (int) $_COOKIE['userid_expire'];

        $userid = (time() < $userid_expire) ? $_COOKIE['unique_userid'] : ade_set_cookie($expire);
    else :
        $userid = ade_set_cookie($expire);
    endif;

    return $userid;
}
