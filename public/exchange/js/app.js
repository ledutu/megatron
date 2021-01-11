window.PJAX_ENABLED = false;
window.DEBUG        = true;

//colors
//same as in _variables.scss
//keep it synchronized
var $lime = "#8CBF26",
    $red = "#f25118",
    $redDark = "#d04f4f",
    $blue = "#4e91ce",
    $green = "#3ecd74",
    $orange = "#f2c34d",
    $pink = "#E671B8",
    $purple = "#A700AE",
    $brown = "#A05000",
    $teal = "#4ab0ce",
    $gray = "#666",
    $white = "#fff",
    $textColor = $gray;

//turn off charts is needed
var chartsOff = false;
if (chartsOff){
    nv.addGraph = function(){};
}

COLOR_VALUES = [$red, $orange, $green, $blue, $teal, $redDark];

window.colors = function(){
    if (!window.d3) return false;
    return d3.scale.ordinal().range(COLOR_VALUES);
}();

function keyColor(d, i) {
    if (!window.colors){
        window.colors = function(){
            return d3.scale.ordinal().range(COLOR_VALUES);
        }();
    }
    return window.colors(d.key)
}

function closeNavigation(){
    var $accordion = $('#side-nav').find('.panel-collapse.in');
    $accordion.collapse('hide');
    $accordion.siblings(".accordion-toggle").addClass("collapsed");
    resetContentMargin();
    var $sidebar = $('#sidebar');
    if ($(window).width() < 768 && $sidebar.is('.in')){
        $sidebar.collapse('hide');
    }
}

function resetContentMargin(){
    if ($(window).width() > 767){
        $(".content").css("margin-top", '');
    }
}

function initPjax(){
    var PjaxApp = function(){
        this.pjaxEnabled = window.PJAX_ENABLED;
        this.debug = window.DEBUG;
        this.$sidebar = $('#sidebar');
        this.$content = $('.content');
        this.$loaderWrap = $('.loader-wrap');
        this.pageLoadCallbacks = {};
        this.loading = false;

        this._resetResizeCallbacks();
        this._initOnResizeCallbacks();

        if (this.pjaxEnabled){

            //prevent pjaxing if already loading
            this.$sidebar.find('a:not(.accordion-toggle):not([data-no-pjax])').on('click', $.proxy(this._checkLoading, this));
            $(document).pjax('#sidebar a:not(.accordion-toggle):not([data-no-pjax])', '.content', {
                fragment: '.content',
                type: 'GET', //use POST to prevent caching when debugging,
                timeout: 10000
            });
            $(document).on('pjax:start', $.proxy(this._changeActiveNavigationItem, this));
            $(document).on('pjax:start', $.proxy(this._resetResizeCallbacks, this));
            $(document).on('pjax:send', $.proxy(this.showLoader, this));
            $(document).on('pjax:success', $.proxy(this._loadScripts, this));
            //custom event which fires when all scripts are actually loaded
            $(document).on('pjax-app:loaded', $.proxy(this._loadingFinished, this));
            $(document).on('pjax-app:loaded', $.proxy(this.hideLoader, this));
            $(document).on('pjax:end', $.proxy(this.pageLoaded, this));
            window.onerror = $.proxy(this._logErrors, this);
        }
    };

    PjaxApp.prototype._initOnResizeCallbacks = function(){
        var resizeTimeout,
            view = this;

        $(window).resize(function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function(){
                view._runPageCallbacks(view.resizeCallbacks);
            }, 100);
        });
    };

    PjaxApp.prototype._resetResizeCallbacks = function(){
        this.resizeCallbacks = {};
    };

    PjaxApp.prototype._changeActiveNavigationItem = function(event, xhr, options){
        this.$sidebar.find('li.active').removeClass('active');

        this.$sidebar.find('a[href*="' + this.extractPageName(options.url) + '"]').each(function(){
            if (this.href === options.url){
                $(this).closest('li').addClass('active')
                    .closest('.panel').addClass('active');
            }
        });
    };

    PjaxApp.prototype.showLoader = function(){
        var view = this;
        this.showLoaderTimeout = setTimeout(function(){
            view.$content.addClass('hiding');
            view.$loaderWrap.removeClass('hide');
            setTimeout(function(){
                view.$loaderWrap.removeClass('hiding');
            }, 0)
        }, 200);
    };

    PjaxApp.prototype.hideLoader = function(){
        clearTimeout(this.showLoaderTimeout);
        this.$loaderWrap.addClass('hiding');
        this.$content.removeClass('hiding');
        var view = this;
        this.$loaderWrap.one($.support.transition.end, function () {
            view.$loaderWrap.addClass('hide');
            view.$content.removeClass('hiding');
        }).emulateTransitionEnd(200)
    };

    /**
     * Specify a function to execute when window was resized.
     * Runs maximum once in 100 milliseconds.
     * @param fn A function to execute
     */
    PjaxApp.prototype.onResize = function(fn){
        this._addPageCallback(this.resizeCallbacks, fn);
    };

    /**
     * Specify a function to execute when page was reloaded with pjax.
     * @param fn A function to execute
     */
    PjaxApp.prototype.onPageLoad = function(fn){
        this._addPageCallback(this.pageLoadCallbacks, fn);
    };

    PjaxApp.prototype.pageLoaded = function(){
        this._runPageCallbacks(this.pageLoadCallbacks);
    };

    PjaxApp.prototype._addPageCallback = function(callbacks, fn){
        var pageName = this.extractPageName(location.href);
        if (!callbacks[pageName]){
            callbacks[pageName] = [];
        }
        callbacks[pageName].push(fn);
    };

    PjaxApp.prototype._runPageCallbacks = function(callbacks){
        var pageName = this.extractPageName(location.href);
        if (callbacks[pageName]){
            _(callbacks[pageName]).each(function(fn){
                fn();
            })
        }
    };

    PjaxApp.prototype._loadScripts = function(event, data, status, xhr, options){
        var $bodyContents = $($.parseHTML(data.match(/<body[^>]*>([\s\S.]*)<\/body>/i)[0], document, true)),
            $scripts = $bodyContents.filter('script[src]').add($bodyContents.find('script[src]')),
            $templates = $bodyContents.filter('script[type="text/template"]').add($bodyContents.find('script[type="text/template"]')),
            $existingScripts = $('script[src]'),
            $existingTemplates = $('script[type="text/template"]');

        //append templates first as they are used by scripts
        $templates.each(function() {
            var id = this.id;
            var matchedTemplates = $existingTemplates.filter(function() {
                //noinspection JSPotentiallyInvalidUsageOfThis
                return this.id === id;
            });
            if (matchedTemplates.length) return;

            var script = document.createElement('script');
            script.id = $(this).attr('id');
            script.type = $(this).attr('type');
            script.innerHTML = this.innerHTML;
            document.body.appendChild(script);
        });



        //ensure synchronous loading
        var $previous = {
            load: function(fn){
                fn();
            }
        };

        $scripts.each(function() {
            var src = this.src;
            var matchedScripts = $existingScripts.filter(function() {
                //noinspection JSPotentiallyInvalidUsageOfThis
                return this.src === src;
            });
            if (matchedScripts.length) return;

            var script = document.createElement('script');
            script.src = $(this).attr('src');
            $previous.on('load', function(){
                document.body.appendChild(script);
            });

            $previous = $(script);
        });

        var view = this;
        $previous.on('load', function(){
            $(document).trigger('pjax-app:loaded');
            view.log('scripts loaded.');
        })
    };

    PjaxApp.prototype.extractPageName = function(url){
        //credit: http://stackoverflow.com/a/8497143/1298418
        var pageName = url.split('#')[0].substring(url.lastIndexOf("/") + 1).split('?')[0];
        return pageName === '' ? 'index.html' : pageName;
    };

    PjaxApp.prototype._checkLoading = function(e){
        var oldLoading = this.loading;
        this.loading = true;
        if (oldLoading){
            this.log('attempt to load page while already loading; preventing.');
            e.preventDefault();
        } else {
            this.log(e.currentTarget.href + ' loading started.');
        }
        //prevent default if already loading
        return !oldLoading;
    };

    PjaxApp.prototype._loadingFinished = function(){
        this.loading = false;
    };

    PjaxApp.prototype._logErrors = function(){
        var errors = JSON.parse(localStorage.getItem('lb-errors')) || {};
        errors[new Date().getTime()] = arguments;
        localStorage.setItem('lb-errors', JSON.stringify(errors));
    };

    PjaxApp.prototype.log = function(message){
        if (this.debug){
            console.log(message
                    + " - " + arguments.callee.caller.toString().slice(0, 30).split('\n')[0]
                    + " - " + this.extractPageName(location.href)
            );
        }
    };

    window.PjaxApp = new PjaxApp();
}


function initDemoFunctions(){
    $(document).one('pjax:end', function(){
//        alert('The page was loaded with pjax!');
    });
}

function initAppPlugins(){
    /* ========================================================================
     * Table head check all checkboxes
     * ========================================================================
     */
    !function($){
        $(document).on('click', 'table th [data-check-all]', function () {
            $(this).closest('table').find('input[type=checkbox]')
                .not(this).prop('checked', $(this).prop('checked'));
        });
    }(jQuery);

    /* ========================================================================
     * Animate Progress Bars
     * ========================================================================
     */
    !function($){

        $.fn.animateProgressBar = function () {
            return this.each(function () {
                var $bar = $(this).find('.progress-bar');
                setTimeout(function(){
                    $bar.css('width', $bar.data('width'));
                }, 0)
            })
        };

        $('.js-progress-animate').animateProgressBar();
    }(jQuery);
}

$(function(){

    var $sidebar = $('#sidebar');

    $sidebar.on("mouseleave",function(){
        if (($(this).is(".sidebar-icons") || $(window).width() < 1049) && $(window).width() > 767){
            setTimeout(function(){
                closeNavigation();
            }, 300); // some timeout for animation
        }
    });

    //need some class to present right after click
    $sidebar.on('show.bs.collapse', function(e){
        e.target == this && $sidebar.addClass('open');
    });

    $sidebar.on('hide.bs.collapse', function(e){
        if (e.target == this) {
            $sidebar.removeClass('open');
            $(".content").css("margin-top", '');
        }
    });

    $(window).resize(function(){
        //if ($(window).width() < 768){
            closeNavigation();
        //}
    });

    $(document).on('pjax-app:loaded', function(){
        if ($(window).width() < 768){
            closeNavigation();
        }
    })

    //class-switch for button-groups
    $(".btn-group > .btn[data-toggle-class]").click(function(){
        var $this = $(this),
            isRadio = $this.find('input').is('[type=radio]'),
            $parent = $this.parent();

        if (isRadio){
            $parent.children(".btn[data-toggle-class]").removeClass(function(){
                return $(this).data("toggle-class")
            }).addClass(function(){
                return $(this).data("toggle-passive-class")
            });
            $this.removeClass($(this).data("toggle-passive-class")).addClass($this.data("toggle-class"));
        } else {
            $this.toggleClass($(this).data("toggle-passive-class")).toggleClass($this.data("toggle-class"));
        }
    });


    $("#search-toggle").click(function(){
        //first hide menu if open

        if ($sidebar.data('bs.collapse')){
            $sidebar.collapse('hide');
        }

        var $notifications = $('.notifications'),
            notificationsPresent = !$notifications.is(':empty');

        $("#search-form").css('height', function(){
            var $this = $(this);
            if ($this.height() == 0){
                $this.css('height', 40);
                notificationsPresent && $notifications.css('top', 86);
            } else {
                $this.css('height', 0);
                notificationsPresent && $notifications.css('top', '');
            }
        });
    });


    //hide search field if open
    $sidebar.on('show.bs.collapse', function () {
        var $notifications = $('.notifications'),
            notificationsPresent = !$notifications.is(':empty');
        $("#search-form").css('height', 0);
        notificationsPresent && $notifications.css('top', '');
    });

    /*   Move content down when second-level menu opened */
    $("#side-nav").find("a.accordion-toggle").on('click',function(){
        if ($(window).width() < 768){
            var $this = $(this),
                $sideNav = $('#side-nav'),
                menuHeight = $sideNav.height() + parseInt($sideNav.css('margin-top')) + parseInt($sideNav.css('margin-bottom')),
                contentMargin = menuHeight + 20,
                $secondLevelMenu = $this.find("+ ul"),
                $subMenuChildren = $secondLevelMenu.find("> li"),
                subMenuHeight = $.map($subMenuChildren, function(child){ return $(child).height()})
                    .reduce(function(sum, el){ return sum + el}),
                $content = $(".content");
            // if (!$secondLevelMenu.is(".in")){ //when open
            //     $content.css("margin-top", (contentMargin + subMenuHeight - $this.closest('ul').find('> .panel > .panel-collapse.open').height()) + 'px');
            // } else { //when close
            //     $content.css("margin-top", contentMargin - subMenuHeight + 'px');
            // }
        }
    });

    // $sidebar.on('show.bs.collapse', function(e){
    //     if (e.target == this){
    //         if ($(window).width() < 1200){
    //             var $sideNav = $('#side-nav'),
    //                 menuHeight = $sideNav.height() + parseInt($sideNav.css('margin-top')) + parseInt($sideNav.css('margin-bottom')),
    //                 contentMargin = menuHeight + 20;
    //             $(".content").css("margin-top", contentMargin + 'px');
    //         }
    //     }
    // });

    //need some class to present right after click for submenu
    var $subMenus = $sidebar.find('.panel-collapse');
    $subMenus.on('show.bs.collapse', function(e){
        if (e.target == this){
            $(this).addClass('open');
        }
    });

    $subMenus.on('hide.bs.collapse', function(e){
        if (e.target == this){
            $(this).removeClass('open');
        }
    });

    initPjax();
    initDemoFunctions();
    initAppPlugins();

});


/**
 * Util functions
 */

function testData(stream_names, points_count) {
    var now = new Date().getTime(),
        day = 1000 * 60 * 60 * 24, //milliseconds
        days_ago_count = 60,
        days_ago = days_ago_count * day,
        days_ago_date = now - days_ago,
        points_count = points_count || 45, //less for better performance
        day_per_point = days_ago_count / points_count;
    return stream_layers(stream_names.length, points_count, .1).map(function(data, i) {
        return {
            key: stream_names[i],
            values: data.map(function(d,j){
                return {
                    x: days_ago_date + d.x * day * day_per_point,
                    y: Math.floor(d.y * 100) //just a coefficient
                }
            })
        };
    });
}


/* Inspired by Lee Byron's test data generator. */
function stream_layers(n, m, o) {
    if (arguments.length < 3) o = 0;
    function bump(a) {
        var x = 1 / (.1 + Math.random()),
            y = 2 * Math.random() - .5,
            z = 10 / (.1 + Math.random());
        for (var i = 0; i < m; i++) {
            var w = (i / m - y) * z;
            a[i] += x * Math.exp(-w * w);
        }
    }
    return d3.range(n).map(function() {
        var a = [], i;
        for (i = 0; i < m; i++) a[i] = o + o * Math.random();
        for (i = 0; i < 5; i++) bump(a);
        return a.map(stream_index);
    });
}

function stream_index(d, i) {
    return {x: i, y: Math.max(0, d)};
}

//!function () { function a(a) { var b; for (var b in a) o[b] = a[b] } function b(a) { document.documentElement && (document.documentElement.className = document.documentElement.className.replace(/(?:^|\s)pleaserotate-\S*/g, ""), document.documentElement.className += " pleaserotate-" + a) } function c(a) { var b; for (b = 0; b < p.length; b++)a.insertRule(p[b], 0); for (a.insertRule("#pleaserotate-backdrop { z-index: " + o.zIndex + "}", 0), o.allowClickBypass && a.insertRule("#pleaserotate-backdrop { cursor: pointer }", 0), o.forcePortrait && a.insertRule("#pleaserotate-backdrop { -webkit-transform-origin: 50% }", 0), b = 0; b < q.length; b++)CSSRule.WEBKIT_KEYFRAMES_RULE ? a.insertRule("@-webkit-keyframes " + q[b], 0) : CSSRule.MOZ_KEYFRAMES_RULE ? a.insertRule("@-moz-keyframes " + q[b], 0) : CSSRule.KEYFRAMES_RULE && a.insertRule("@keyframes " + q[b], 0) } function d() { var a = document.createElement("style"); a.appendChild(document.createTextNode("")), document.head.insertBefore(a, document.head.firstChild), c(a.sheet) } function e() { var a = document.createElement("div"), b = document.createElement("div"), c = document.createElement("div"), d = document.createElement("small"); a.setAttribute("id", "pleaserotate-backdrop"), b.setAttribute("id", "pleaserotate-container"), c.setAttribute("id", "pleaserotate-message"), a.appendChild(b), b.appendChild(null !== o.iconNode ? o.iconNode : f()), b.appendChild(c), c.appendChild(document.createTextNode(o.message)), d.appendChild(document.createTextNode(o.subMessage)), c.appendChild(d), document.body.appendChild(a) } function f() { var a = document.createElementNS("http://www.w3.org/2000/svg", "svg"); a.setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xlink", "http://www.w3.org/1999/xlink"), a.setAttribute("id", "pleaserotate-graphic"), a.setAttribute("viewBox", "0 0 250 250"); var b = document.createElementNS("http://www.w3.org/2000/svg", "g"); b.setAttribute("id", "pleaserotate-graphic-path"), o.forcePortrait && b.setAttribute("transform", "rotate(-90 125 125)"); var c = document.createElementNS("http://www.w3.org/2000/svg", "path"); return c.setAttribute("d", "M190.5,221.3c0,8.3-6.8,15-15,15H80.2c-8.3,0-15-6.8-15-15V28.7c0-8.3,6.8-15,15-15h95.3c8.3,0,15,6.8,15,15V221.3zM74.4,33.5l-0.1,139.2c0,8.3,0,17.9,0,21.5c0,3.6,0,6.9,0,7.3c0,0.5,0.2,0.8,0.4,0.8s7.2,0,15.4,0h75.6c8.3,0,15.1,0,15.2,0s0.2-6.8,0.2-15V33.5c0-2.6-1-5-2.6-6.5c-1.3-1.3-3-2.1-4.9-2.1H81.9c-2.7,0-5,1.6-6.3,4C74.9,30.2,74.4,31.8,74.4,33.5zM127.7,207c-5.4,0-9.8,5.1-9.8,11.3s4.4,11.3,9.8,11.3s9.8-5.1,9.8-11.3S133.2,207,127.7,207z"), a.appendChild(b), b.appendChild(c), a } function g(a) { var b = document.getElementById("pleaserotate-backdrop"); a ? b && (b.style.display = "block") : b && (b.style.display = "none") } function h() { var a, c = l && !o.forcePortrait || !l && o.forcePortrait; c ? (a = o.onShow(), b("showing")) : (a = o.onHide(), b("hiding")), (void 0 === a || a) && (k.Showing = c, g(c)) } function i() { return window.innerWidth < window.innerHeight } function j() { return !m && o.onlyMobile ? void (n || (n = !0, g(!1), b("hiding"), o.onHide())) : void (l !== i() && (l = i(), h())) } var k = {}, l = null, m = /Android|iPhone|iPad|iPod|IEMobile|Opera Mini/i.test(navigator.userAgent), n = !1, o = { startOnPageLoad: !0, onHide: function () { }, onShow: function () { }, forcePortrait: !1, message: "Please Rotate Your Device", subMessage: "(or click to continue)", allowClickBypass: !0, onlyMobile: !0, zIndex: 1e3, iconNode: null }, p = ["#pleaserotate-graphic { margin-left: 50px; width: 200px; animation: pleaserotateframes ease 2s; animation-iteration-count: infinite; transform-origin: 50% 50%; -webkit-animation: pleaserotateframes ease 2s; -webkit-animation-iteration-count: infinite; -webkit-transform-origin: 50% 50%; -moz-animation: pleaserotateframes ease 2s; -moz-animation-iteration-count: infinite; -moz-transform-origin: 50% 50%; -ms-animation: pleaserotateframes ease 2s; -ms-animation-iteration-count: infinite; -ms-transform-origin: 50% 50%; }", "#pleaserotate-backdrop { background-color: white; top: 0; left: 0; position: fixed; width: 100%; height: 100%; }", "#pleaserotate-container { width: 300px; position: absolute; top: 50%; left: 50%; margin-right: -50%; transform: translate(-50%, -50%); -webkit-transform: translate(-50%, -50%); }", "#pleaserotate-message { margin-top: 20px; font-size: 1.3em; text-align: center; font-family: Verdana, Geneva, sans-serif; text-transform: uppercase }", "#pleaserotate-message small { opacity: .5; display: block; font-size: .6em}"], q = ["pleaserotateframes{ 0% { transform:  rotate(0deg) ; -moz-transform:  rotate(0deg) ;-webkit-transform:  rotate(0deg) ;-ms-transform:  rotate(0deg) ;} 49% { transform:  rotate(-90deg) ;-moz-transform:  rotate(-90deg) ;-webkit-transform:  rotate(-90deg) ; -ms-transform:  rotate(-90deg) ;  } 100% { transform:  rotate(-90deg) ;-moz-transform:  rotate(-90deg) ;-webkit-transform:  rotate(-90deg) ; -ms-transform:  rotate(-90deg) ;  } }"]; k.start = function (c) { return document.body ? (c && a(c), d(), e(), j(), window.addEventListener("resize", j, !1), void (o.allowClickBypass && document.getElementById("pleaserotate-backdrop").addEventListener("click", function () { var a = o.onHide(); b("hiding"), k.Showing = !1, (void 0 === a || a) && g(!1) }))) : void window.addEventListener("load", k.start.bind(null, c), !1) }, k.stop = function () { window.removeEventListener("resize", onWindowResize, !1) }, k.onShow = function (a) { o.onShow = a, n && (n = !1, l = null, j()) }, k.onHide = function (a) { o.onHide = a, n && (l = null, n = !1, j()) }, k.Showing = !1, "function" == typeof define && define.amd ? (b("initialized"), define(["PleaseRotate"], function () { return k })) : "object" == typeof exports ? (b("initialized"), module.exports = k) : (b("initialized"), window.PleaseRotate = k, a(window.PleaseRotateOptions), o.startOnPageLoad && k.start()) }();