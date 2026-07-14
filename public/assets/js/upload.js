$(document).ready(function () {

    $("form").on("submit", function () {

        Swal.fire({
            title: "Uploading...",
            text: "Please wait while the Excel file is uploaded.",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

    });

});