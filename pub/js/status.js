function updateStatus() {
    $.getJSON(
        $(location).attr('href'),
        {},
        function (result) {
            $('#status-message').html(result.statusMessage);
            $('#status').html(
                result.isUpdateInProgress ? "Update application is running" : "Update application is NOT running"
            );
        }
    );
}
$(document).ready(setInterval(updateStatus, 3000));
