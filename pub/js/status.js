function updateStatus() {
    $.ajax({
        success: function (result) {
            $('#status-message').html(result);
        }
    });
}
$(document).ready(setInterval(updateStatus, 3000));
