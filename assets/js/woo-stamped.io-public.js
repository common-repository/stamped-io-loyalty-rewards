/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function Slarm_myInit() { 
    StampedFn.init({
        apiKey: Woo_stamped.pub_key,
        storeUrl: Woo_stamped.url
    });
}
(function e() { var e = document.createElement("script"); e.type = "text/javascript", e.async = true, e.onload = Slarm_myInit, e.src = "//cdn1.stamped.io/files/widget.min.js"; var t = document.getElementsByTagName("script")[0]; t.parentNode.insertBefore(e, t) })();