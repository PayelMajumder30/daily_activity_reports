$(document).ready(function () {

    $("#btnSearch").click(function () {
        console.log("Search clicked.");
    });

    $("#btnReset").click(function () {

        $("#engineer").val('');
        $("#status").val('');
        $("#date").val('');
        $("#complaint").val('');

    });

});