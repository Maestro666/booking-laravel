function datesBetween(startDt, endDt) {
    var between = [];
    var currentDate = new Date(startDt);
    var end = new Date(endDt);
    while (currentDate <= end) {
        between.push($.datepicker.formatDate('mm/dd/yy', new Date(currentDate)));
        currentDate.setDate(currentDate.getDate() + 1);
    }

    return between;
}


var Ajax = {

    get: function get(url, _success) {
        var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

        var _beforeSend = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

        $.ajax({

            cache: false,
            url: base_url + '/' + url,
            type: "GET",
            data: data,
            success: function success(response) {

                App[_success](response);
            },
            beforeSend: function beforeSend() {

                if (_beforeSend) App[_beforeSend]();
            }

        });
    },

    
    set: function set() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var url = arguments[1];

        var _success2 = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

        $.ajax({

            cache: false,
            url: base_url + '/' + url,
            type: "GET",
            dataType: "json",
            data: data,
            success: function success(response) {

                if (_success2) App[_success2](response);
            }

        });
    }

};


var App = {

    timestamp: null, 

    GetReservationData: function GetReservationData(id, calendar_id , date) {

        App.calendar_id = calendar_id; 
        Ajax.get('ajaxGetReservationData?fromWebApp=1', 'AfterGetReservationData', { room_id: id, date: date }, 'BeforeGetReservationData');
    },
    BeforeGetReservationData: function BeforeGetReservationData() {

        $('.loader_' + App.calendar_id).hide(); 
        $('.hidden_' + App.calendar_id).show(); 
    },
    AfterGetReservationData: function AfterGetReservationData(response) {

        $('.hidden_' + App.calendar_id + " .reservation_data_room_number").html(response.room_number); 

        $('.hidden_' + App.calendar_id + " .reservation_data_day_in").html(response.day_in); 
        $('.hidden_' + App.calendar_id + " .reservation_data_day_out").html(response.day_out); 
        $('.hidden_' + App.calendar_id + " .reservation_data_person").html(response.FullName); 
        $('.hidden_' + App.calendar_id + " .reservation_data_person").attr('href', response.userLink); 
        $('.hidden_' + App.calendar_id + " .reservation_data_delete_reservation").attr('href', response.deleteResLink); 

        
        if (response.status) {
            $('.hidden_' + App.calendar_id + " .reservation_data_confirm_reservation").removeAttr('href');
            $('.hidden_' + App.calendar_id + " .reservation_data_confirm_reservation").attr('disabled', 'disabled');
        } else {
            $('.hidden_' + App.calendar_id + " .reservation_data_confirm_reservation").attr('href', response.confirmResLink);
        }
    },

    
    SetReadNotification: function SetReadNotification(id) {

        Ajax.set({ id: id }, 'ajaxSetReadNotification?fromWebApp=1');
    },

    
    GetNotShownNotifications: function GetNotShownNotifications() {

        
        Ajax.get("ajaxGetNotShownNotifications?fromWebApp=1&timestamp=" + App.timestamp, 'AfterGetNotShownNotifications');
    },

    
    AfterGetNotShownNotifications: function AfterGetNotShownNotifications(response) {

        alert(1);
    }

};


$(document).on('click', '.dropdown', function (e) {
    e.stopPropagation();
});


$(document).on("click", ".unread_notification", function (event) {

    event.preventDefault();

    $(this).removeClass('unread_notification');

    var ncount = parseInt($('#app-notifications-count').html());

    if (ncount > 0) {
        $('#app-notifications-count').html(ncount - 1);

        if (ncount == 1) $('#app-notifications-count').hide();
    }

    var idOfNotification = $(this).children().attr('href');
    $(this).children().removeAttr('href');
    App.SetReadNotification(idOfNotification);
});


$(function () {

    App.GetNotShownNotifications();
});