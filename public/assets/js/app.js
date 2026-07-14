$(document).ready(function () {

    if ($(".datepicker").length) {
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d"
        });
    }

});