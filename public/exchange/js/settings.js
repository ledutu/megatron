function triggerChartsResize(){try{window.onresize&&window.onresize()}catch(e){}$(window).trigger("resize")}$(function(){var e=$("#settings"),t=$("#sidebar-settings"),n=JSON.parse(localStorage.getItem("settings-state"))||{sidebar:"left",sidebarState:"auto",displaySidebar:!0},i=$(".page-header"),a=$("body"),s=function(){e.data("bs.popover").hoverState="out",e.popover("hide")},r=function(t){var n=e.siblings(".popover");n.length&&!$.contains(n[0],t.target)&&(s(),$(document).off("click",r))},o=function(e){"right"==e?a.addClass("sidebar-on-right"):a.removeClass("sidebar-on-right")},d=function(e,n){var i=$("#sidebar-settings-template");n=null==n,i[0]&&(t.html(_.template(i.html())({sidebarState:e})),"auto"==e?$(".sidebar, .side-nav, .wrap, .logo").removeClass("sidebar-icons"):$(".sidebar, .side-nav, .wrap, .logo").addClass("sidebar-icons"),n&&triggerChartsResize())},l=function(e,t){t=null==t,1==e?a.removeClass("sidebar-hidden"):a.addClass("sidebar-hidden"),t&&triggerChartsResize()};o(n.sidebar),d(n.sidebarState,!1),l(n.displaySidebar,!1),e[0]&&(e.popover({template:'<div class="popover settings-popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"></div></div></div>',html:!0,animation:!1,placement:"bottom",content:function(){return _.template($("#settings-template").html())(n)}}).click(function(e){return console.log("2"),$(".page-header .dropdown.open .dropdown-toggle").dropdown("toggle"),$(document).on("click",r),$(this).focus(),!1}),$(".page-header .dropdown-toggle").click(function(){s(),console.log("wtf1"),$(document).off("click",r)}),i.on("click",".popover #sidebar-toggle .btn",function(){var e=$(this).data("value");o(e),n.sidebar=e,localStorage.setItem("settings-state",JSON.stringify(n))}),i.on("click",".popover #display-sidebar-toggle .btn",function(){var e=$(this).data("value");l(e),n.displaySidebar=e,localStorage.setItem("settings-state",JSON.stringify(n))}),t.on("click",".btn",function(){var e=$(this).data("value");"icons"==e&&closeNavigation(),d(e),n.sidebarState=e,localStorage.setItem("settings-state",JSON.stringify(n))}),($("#sidebar").is(".sidebar-icons")||$(window).width()<1049)&&$(window).width()>767&&closeNavigation(),i.on("click",".popover [data-toggle='buttons-radio'] .btn:not(.active)",function(){var e=$(this);e.parent().find(".btn").removeClass("active"),setTimeout(function(){e.addClass("active")},0)}))}),window.LightBlue={screens:{"xs-max":767,"sm-min":768,"sm-max":991,"md-min":992,"md-max":1199,"lg-min":1200},isScreen:function(e){var t=window.innerWidth;return(t>=this.screens[e+"-min"]||"xs"==e)&&(t<=this.screens[e+"-max"]||"lg"==e)},getScreenSize:function(){var e=window.innerWidth;return e<=this.screens["xs-max"]?"xs":e>=this.screens["sm-min"]&&e<=this.screens["sm-max"]?"sm":e>=this.screens["md-min"]&&e<=this.screens["md-max"]?"md":e>=this.screens["lg-min"]?"lg":void 0},changeColor:function(e,t,n){var i=function(e,t){for(e+="";e.length<t;)e="0"+e;return e};e=(e=e.replace(/^\s*|\s*$/,"")).replace(/^#?([a-f0-9])([a-f0-9])([a-f0-9])$/i,"#$1$1$2$2$3$3");var a=Math.round(256*t)*(n?-1:1),s=e.match(new RegExp("^rgba?\\(\\s*(\\d|[1-9]\\d|1\\d{2}|2[0-4][0-9]|25[0-5])\\s*,\\s*(\\d|[1-9]\\d|1\\d{2}|2[0-4][0-9]|25[0-5])\\s*,\\s*(\\d|[1-9]\\d|1\\d{2}|2[0-4][0-9]|25[0-5])(?:\\s*,\\s*(0|1|0?\\.\\d+))?\\s*\\)$","i")),r=s&&null!=s[4]?s[4]:null,o=s?[s[1],s[2],s[3]]:e.replace(/^#?([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i,function(){return parseInt(arguments[1],16)+","+parseInt(arguments[2],16)+","+parseInt(arguments[3],16)}).split(/,/);return s?"rgb"+(null!==r?"a":"")+"("+Math[n?"max":"min"](parseInt(o[0],10)+a,n?0:255)+", "+Math[n?"max":"min"](parseInt(o[1],10)+a,n?0:255)+", "+Math[n?"max":"min"](parseInt(o[2],10)+a,n?0:255)+(null!==r?", "+r:"")+")":["#",i(Math[n?"max":"min"](parseInt(o[0],10)+a,n?0:255).toString(16),2),i(Math[n?"max":"min"](parseInt(o[1],10)+a,n?0:255).toString(16),2),i(Math[n?"max":"min"](parseInt(o[2],10)+a,n?0:255).toString(16),2)].join("")},lighten:function(e,t){return this.changeColor(e,t,!1)},darken:function(e,t){return this.changeColor(e,t,!0)}};