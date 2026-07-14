$(document).ready(function () {

    if ($("#complaintTable tbody tr").length > 0) {

        $("#complaintTable").DataTable({
            pageLength: 10,
            ordering: true,
            searching: true
        });

    }

});