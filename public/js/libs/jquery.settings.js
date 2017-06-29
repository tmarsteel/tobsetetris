/*!
* jQuery Cookie Plugin v1.4.1
* https://github.com/carhartl/jquery-cookie
*
* Copyright 2013 Klaus Hartl
* Released under the MIT license
*/
(function (factory) {
if (typeof define === 'function' && define.amd) {
// AMD
define(['jquery'], factory);
} else if (typeof exports === 'object') {
// CommonJS
factory(require('jquery'));
} else {
// Browser globals
factory(jQuery);
}
}(function ($) {

var pluses = /\+/g;

function encode(s) {
return config.raw ? s : encodeURIComponent(s);
}

function decode(s) {
return config.raw ? s : decodeURIComponent(s);
}

function stringifyCookieValue(value) {
return encode(config.json ? JSON.stringify(value) : String(value));
}

function parseCookieValue(s) {
if (s.indexOf('"') === 0) {
// This is a quoted cookie as according to RFC2068, unescape...
s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
}

try {
// Replace server-side written pluses with spaces.
// If we can't decode the cookie, ignore it, it's unusable.
// If we can't parse the cookie, ignore it, it's unusable.
s = decodeURIComponent(s.replace(pluses, ' '));
return config.json ? JSON.parse(s) : s;
} catch(e) {}
}

function read(s, converter) {
var value = config.raw ? s : parseCookieValue(s);
return $.isFunction(converter) ? converter(value) : value;
}

var config = $.cookie = function (key, value, options) {

// Write

if (value !== undefined && !$.isFunction(value)) {
options = $.extend({}, config.defaults, options);

if (typeof options.expires === 'number') {
var days = options.expires, t = options.expires = new Date();
t.setTime(+t + days * 864e+5);
}

return (document.cookie = [
encode(key), '=', stringifyCookieValue(value),
options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
options.path ? '; path=' + options.path : '',
options.domain ? '; domain=' + options.domain : '',
options.secure ? '; secure' : ''
].join(''));
}

// Read

var result = key ? undefined : {};

// To prevent the for loop in the first place assign an empty array
// in case there are no cookies at all. Also prevents odd result when
// calling $.cookie().
var cookies = document.cookie ? document.cookie.split('; ') : [];

for (var i = 0, l = cookies.length; i < l; i++) {
var parts = cookies[i].split('=');
var name = decode(parts.shift());
var cookie = parts.join('=');

if (key && key === name) {
// If second argument (value) is a function it's a converter...
result = read(cookie, value);
break;
}

// Prevent storing a cookie that we couldn't decode.
if (!key && (cookie = read(cookie)) !== undefined) {
result[name] = cookie;
}
}

return result;
};

config.defaults = {};

$.removeCookie = function (key, options) {
if ($.cookie(key) === undefined) {
return false;
}

// Must not alter options, thus extending a fresh object...
$.cookie(key, '', $.extend({}, options, { expires: -1 }));
return !$.cookie(key);
};

}));

(function($, undefined) {
    var handlers = [];

    $.settings = function(s_name, s_value)
    {   
        if (s_value == undefined)
        {
            var value = $.cookie(s_name);
            if (value == undefined)
            {
                value = $.settings.get(s_name);
                if (value != undefined)
                {
                    $.cookie(s_name, value, $.settings.cookieOptions);
                }
            }
            return value;
        }
        else
        {
            $.cookie(s_name, s_value, $.settings.cookieOptions);
            $.settings.set(s_name, s_value);
            if (handlers[s_name] != undefined)
            {
                $.each(handlers[s_name], function(index, handler) {
                    handler(s_name, s_value);
                });
            }
        }
    };
    $.settings.setURL = "";
    $.settings.getURL = "";
    $.settings.cookieOptions = {
        expires: 360,
        path: location.href
    };
    $.settings.set = function(s_name, s_value, options)
    {
        var defAjaxOpts = {
            url: $.settings.setURL,
            type: "POST",
            data: {
                name: s_name,
                value: s_value
            }
        };

        if (options != undefined)
        {
            $.extend(defAjaxOpts, options);
        }
        $.ajax(defAjaxOpts);
    };
    $.settings.get = function(s_name, options)
    {
        var value = undefined,
            defAjaxOpts = {
            url: $.settings.getURL,
            type: "POST",
            async: false,
            data: {
                name: s_name
            },
            success: function(_v) {
                value = _v;
            }
        };

        if (options != undefined)
        {
            if (options.data)
            {
                $.extend(defAjaxOpts.data, options.data);
                options.data = defAjaxOpts.data;
            }
            $.extend(defAjaxOpts.data, options);
        }
        
        $.ajax(defAjaxOpts);
        
        return value;
    };
    $.settings.onSettingSet = function(s_name, handler)
    {
        if (handlers[s_name] == undefined)
        {
            handlers[s_name] = [handler];
        }
        else
        {
            handlers[s_name].push(handler);
        }
    };
})(jQuery);