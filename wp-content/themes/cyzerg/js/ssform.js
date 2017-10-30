(function(){

    var version = '2.0.01';
    var protocol = ('https:' == document.location.protocol ? 'https://' : 'http://');
    var form_location = protocol;
    var hostname = location.hostname;
    var referrer = document.referrer;
    var hiddenFields = [];
    var iframe, formID;

    var Cookie_Session_Tracking = '__ss_tk';


    // Write to page
    var addForm = function(form) {

        var trackingID = getCookie(Cookie_Session_Tracking);

        if (trackingID) {
            hiddenFields.push('_tk=' + trackingID);
        }

        var hiddenFieldParams = hiddenFields.length ? '?' + hiddenFields.join('&') : '';

        var form = ['<iframe id="ssf_', form.formID ,'" src="', form_location, form.account, '/',
            form.formID, hiddenFieldParams, '" style="overflow-y: auto" frameborder="no" height="', form.height || 1000, '" width="',
            form.width || 500, '"></iframe>'].join('');
            
        document.write(form);
    };


    var getCookie = function(cookie_name) {

        if (cookie_name) {
            var results = document.cookie.match ( '(^|;) ?' + cookie_name + '=([^;]*)(;|$)' );

            if ( results ) {
                return ( unescape ( results[2] ) );
            }

            return null;

        }

        return document.cookie;
    };

    /* Redirect and Resize Handling */
    var handleMessage = function(ev) {

        var data = ev.data;

        if (data && data.action == 'redirect' && data.url ) {
            window.top.location = data.url;
        }

        if (data && data.formID && data.formID == formID) {

            iframe = document.getElementById('ssf_' + data.formID);

            if (data['force']) {
                iframe.height = data.height || iframe.height;
            } else {
                iframe.height = Math.max(data.height, iframe.height);
            }

            // Go at least to layers up to make sure they adjust to the iframe size
            if (iframe.parentNode) {
                iframe.parentNode.style.minHeight = data.height + 'px';
                if (iframe.parentNode.parentNode) {
                    iframe.parentNode.parentNode.style.minHeight = data.height + 'px';
                }
            }
        }

    };


    var init = function() {

        formID = ss_form.formID;

        // Set the Domain
        if (ss_form && ss_form.domain) {
            form_location += ss_form.domain + '/prospector/form/';
        } else {
            form_location += 'app.sharpspring.com/prospector/form/';
        }

        // Add hidden fields
        if (ss_form && ss_form.hidden) {
            for (var hiddenKey in ss_form.hidden) {
                hiddenFields.push(encodeURIComponent(hiddenKey) + '=' + encodeURIComponent(ss_form.hidden[hiddenKey]));
            }
        }

        if (document.referrer) {
            hiddenFields.push('rf__sb=' + encodeURIComponent(document.referrer));
        }

        if(typeof window.addEventListener != 'undefined') {
            window.addEventListener('message', handleMessage, false);
        }
        else if(typeof window.attachEvent != 'undefined') {
            window.attachEvent('onmessage', handleMessage);
        }

        addForm(ss_form);

    }

    init();

})();

