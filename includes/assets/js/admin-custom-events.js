// File: assets/js/admin-custom-events.js
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('wpchill-analytics-custom-events');
    const template = document.getElementById('custom-event-template').innerHTML;
    let index = container.children.length;

    document.getElementById('add-custom-event').addEventListener('click', function() {
        const newRow = template.replace(/{{index}}/g, index);
        container.insertAdjacentHTML('beforeend', newRow);
        index++;
    });

    container.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-custom-event')) {
            event.target.closest('.custom-event-row').remove();
        }
    });
});