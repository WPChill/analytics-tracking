// File: assets/js/frontend-custom-events.js
(function () {
    function initCustomEvents() {
        if (typeof wpchillAnalyticsEvents === 'undefined' || typeof umami === 'undefined') {
            console.warn('WPChill Analytics: Required variables are not defined');
            return;
        }

        wpchillAnalyticsEvents.forEach(function (customEvent) {
            var elements = document.querySelectorAll(customEvent.selector);

            elements.forEach(function (element) {
                element.addEventListener('click', function (e) {
                    trackUmamiEvent(customEvent.name, customEvent.selector, e);
                });
            });
        });
    }

    function trackUmamiEvent(eventName, eventSelector, event) {
        if (typeof umami.track !== 'function') {
            console.warn('WPChill Analytics: Umami tracking function not found');
            return;
        }

        var eventData = {
            selector: eventSelector,
            tagName: event.target.tagName,
            className: event.target.className,
            id: event.target.id,
            url: window.location.href,
            path: window.location.pathname
        };

        umami.track(eventName, eventData);
        console.log('WPChill Analytics: Tracked event', eventName, eventData);
    }

    if (document.readyState === 'complete') {
        initCustomEvents();
    } else {
        window.addEventListener('load', initCustomEvents);
    }
})();