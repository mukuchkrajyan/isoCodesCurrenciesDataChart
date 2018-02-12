function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}

/*Highcharts drawing*/
function drawChart() {

    options = {
        data: {
            table: document.getElementById('datatable'),
            switchRowsAndColumns: true
        },
        chart: {
            type: 'column',
            renderTo: 'chartResult',

        },
        title: {
            text: 'IsoCodes And Dates Relation'
        },
        subtitle: {
            text: 'Shows each IsoCode Value for every date'
        }
        ,
        yAxis: {
            allowDecimals: false,
            title: {
                text: 'Units'
            }
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.series.name + '</b><br/>' + this.point.y + ' ' + this.point.name.toLowerCase();
            },
            useHTML: true
        },
        plotOptions: {
            column: {
                dataLabels: {
                    enabled: true,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                }
            },
            bar: {
                dataLabels: {
                    enabled: true,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                }
            }
        }
    };


    chart = new Highcharts.Chart(options);

    $('#change_chart_type').change(function () {

        new_chart_type = $(this).val();

        if (new_chart_type == "stacked-column") {

            new_chart_type = "column";

            stackingOptions = options.plotOptions.column;

            options.plotOptions.column.stacking = 'normal';

            options.plotOptions.column.dataLabels.enabled = true;
        }
        else if (new_chart_type == "stacked-bar") {

            new_chart_type = "bar";

            stackingOptions = options.plotOptions.bar;

            options.plotOptions.bar.stacking = 'normal';

            options.plotOptions.bar.dataLabels.enabled = true;

        }
        else if (new_chart_type.indexOf('stacked') == -1) {

            options.plotOptions.bar.stacking = '';

            options.plotOptions.column.stacking = '';
        }
        $("#current_chart_type").val(new_chart_type);

        options.chart.type = new_chart_type;

        $("#chart_type").val(new_chart_type);


        $('#chartResult').highcharts(Highcharts.merge(options));
    });

    $('#chartDownloadPdf').click(function () {

        $(".highcharts-contextbutton").click();

        item = $(".highcharts-menu-item")[3];

        if (item.innerText.indexOf("SVG") == -1) {
            $(".highcharts-menu-item")[3].click();
        }
        else {
            $(".highcharts-menu-item")[4].click();
        }
    });

    $('#chartDownloadPng').click(function () {

        $(".highcharts-contextbutton").click();

        $(".highcharts-menu-item")[1].click();
    });

    $('#chartDownloadExcel').click(function () {

        $(".highcharts-contextbutton").click();

        $(".highcharts-menu-item")[6].click();
    });
}

function main(implodedIsoCodes, isoCodesCountriesEncoded) {

    /*Check Uncheck all isoCodes Checkboxes*/
    $("#check_uncheck_all").click(function () {
        console.log($('.checkIsoCode').prop('checked'));

        if ($('.checkIsoCode').prop('checked') == false) {
            $('.checkIsoCode').attr('checked', 'checked');
            $('.checkIsoCode').prop('checked', true);
        }
        else {
            $('.checkIsoCode').attr('checked', '');
            $('.checkIsoCode').prop('checked', false);
        }

    });

    $("#getResults").click(function () {

            getResults = true;   //  access to get results, no errors

            /*Getting dates*/
            dateFrom = $("#dateFrom").val();

            dateTo = $("#dateTo").val();

            if ($("#tasks").val() == 1) {

                $("#results").html("<p class='isoCodes'>" + implodedIsoCodes + "</p>");

                $('html, body').animate({
                    scrollTop: $("#results").offset().top
                }, 2000);
            }
            else if ($("#tasks").val() == 2) {
                $("#results").html("<p class='isoCodesCountries'>" + isoCodesCountriesEncoded + "</p>");

                $('html, body').animate({
                    scrollTop: $("#results").offset().top
                }, 2000);
            }
            else if ($("#tasks").val() == 3 || $("#tasks").val() == 4) {

                if (dateFrom.trim() == "" || dateTo.trim() == "") {
                    $("#errShow").text("Please choose date");

                    $('html, body').animate({
                        scrollTop: $("#errShow").offset().top
                    }, 2000);

                    $("#errShow").show("fast");

                    getResults = false;
                }
                else if (dateFrom > dateTo) {
                    $("#errShow").text("Please make sure... dateTo must be later date");

                    $('html, body').animate({
                        scrollTop: $("#errShow").offset().top
                    }, 2000);

                    $("#errShow").show("slow");

                    getResults = false;

                }
                else {
                    $("#errShow").hide("fast");
                }

                if ($("input:checked").length == 0) {

                    $("#errCheckboxes").text("No Isocodes Checked...Please check at least one isoCode");

                    $('html, body').animate({
                        scrollTop: $("#errCheckboxes").offset().top
                    }, 2000);

                    $("#errCheckboxes").show("slow");

                    getResults = false;

                }
                else {
                    $("#errCheckboxes").hide("fast");
                }

                if (getResults == true) {

                    checkedIsoCodes = [];

                    dateName = ['Date'];

                    $("input:checked").each(function () {
                        checkedIsoCodes.push($(this).val());
                    });

                    implodedIsoCodes = checkedIsoCodes.join(',');

                    implodedDateNameAndIsoCodes = dateName.concat(checkedIsoCodes);

                    $("#datatableHeadTr").html('');

                    $("#datatableTbody").html('');

                    $.get("/get-dates-interval-iso-data/", {

                            _token: '{{ csrf_token() }}',

                            dateFrom: dateFrom,

                            dateTo: dateTo,

                            implodedIsoCodes: implodedIsoCodes

                        }, function (result) {

                            result = JSON.parse(result);

                            isoCodesRespose = [];

                            $.each(result, function (key, val) {

                                $.each(val, function (key, value) {

                                    $.each(value, function (key, valueEnd) {
                                        if (isoCodesRespose.indexOf(key) == -1) {
                                            isoCodesRespose.push(key);

                                        }
                                    });
                                });
                            });

                            $("#datatableHeadTr").append("<th>" + "Date" + "</th>");

                            for (i = 0; i < isoCodesRespose.length; i++) {
                                $("#datatableHeadTr").append("<th>" + isoCodesRespose[i] + "</th>");
                            }

                            $.each(result, function (key, val) {

                                curr_row = "<tr>";

                                curr_row += "<td>" + key + "</td>";

                                $.each(val, function (key, value) {

                                    $.each(value, function (key, valueEnd) {
                                        curr_row += "<td>" + valueEnd + "</td>";
                                    });

                                });

                                curr_row += "</tr>";

                                $("#datatableTbody").append(curr_row);
                            });

                            if ($("#tasks").val() == 4) {

                                $(".chartAndOther").show();

                                drawChart();
                            }
                            // var jsonObj = JSON.parse(result);

                            // var jsonPretty = JSON.stringify(jsonObj, null, '\t');

                            $('html, body').animate({
                                scrollTop: $("#results").offset().top
                            }, 1000);
                        }
                    );

                }

            }
        }
    );
}

$(document).ready(function () {

    $("#dateTo").val(formatDate(new Date()));

});