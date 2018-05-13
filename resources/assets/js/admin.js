var eventDates = {};
var datesConfirmed = ['12/12/2017', '12/13/2017', '12/14/2017'];
var datesnotConfirmed = ['12/20/2017', '12/21/2017', '12/22/2017', '12/25/2017'];


for (var i = 0; i < datesConfirmed.length; i++)
{
    eventDates[ datesConfirmed[i] ] = 'confirmed';
}

var tmp = {};
for (var i = 0; i < datesnotConfirmed.length; i++)
{
    tmp[ datesnotConfirmed[i] ] = 'notconfirmed';
}


Object.assign(eventDates, tmp);


$(function () {
    $(".reservation_calendar").datepicker({
        onSelect: function (data) {

            var a = $(this).attr('id');

            $('.hidden_' + a).hide();
            $('.loader_' + a).show();

            setTimeout(function () {

                $('.loader_' + a).hide();
                $('.hidden_' + a).show();

            }, 1000);

        },
        beforeShowDay: function (date)
        {
            var tmp = eventDates[ $.datepicker.formatDate('mm/dd/yy', date)];
//            console.log(tmp);
            if (tmp)
            {
                if (tmp == 'confirmed')
                    return [true, 'reservationconfirmed'];
                else
                    return [true, 'reservationnotconfirmed'];
            } else
                return [false, ''];

        }


    });
});
