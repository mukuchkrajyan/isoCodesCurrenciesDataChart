@extends('layouts.default')

@section('content')
    <div class="container">
        <div class="row">
            <form class="form-group" action="post" method="post">
                <label for="tasks">Select list (select one):</label>

                <select class="form-control" id="tasks">
                    <option value="1">Կենտրոնական Բանկի (cba.am) կայքից ստանալ տարադրամները</option>

                    <option value="2">Կենտրոնական Բանկի (cba.am) կայքից ստանալ տարադրամները և
                        երկիրներըհամապատասխանաբար
                    </option>

                    <option value="3">Կենտրոնական Բանկի (cba.am) կայքից ստանալ նշված տարադրամների համար նշված
                        միջակայքերում փոխարժեքները
                    </option>

                    <option value="4">Կառուցել գրաֆիկ</option>
                </select>

                <p id="errShow" class="err"></p>

                <div class="datesContent">
                    <label for="dateFrom">From :</label>

                    <input type="date" class="date form-control" id="dateFrom" data-date=""
                           data-date-format="DD MMMM YYYY">

                    <label for="dateTo">To :</label>

                    <input type="date" class="date form-control" id="dateTo">
                </div>

                <p id="errCheckboxes" class="err"></p>

                <div id="checkboxesContent">
                    <input id="check_uncheck_all" class="checkUncheckIsoCodes" type="checkbox" name="vehicle" value=""
                           checked><span
                            class="check_uncheck_all ">Check/Uncheck All</span><br>

                    @foreach ($isoCodesCountries as $key=>$value)
                        <input class="checkIsoCode" type="checkbox" name="vehicle" checked
                               value="{{$value["isoCode"]}}"><span
                                class="checkIsoCodeValue">{{$value["isoCodeCountry"]."( ".$value["isoCode"]." )"}}</span>
                        <br>
                    @endforeach
                </div>

                <div id="getResults">
                    <input id="getResults" class="btn btn-success continue" type="button" value="Get Results">
                </div>
            </form>


            <div id="results">

                <div class="container">
                    <table id="datatable" class="table table-bordered">
                        <thead>
                        <tr id="datatableHeadTr">

                        </tr>
                        </thead>
                        <tbody id="datatableTbody">

                        </tbody>
                    </table>

                    <input type="hidden" id="chart_type" name="chart_type"/>
                    <input type="hidden" class="" name="current_chart_type" id="current_chart_type" value="column"/>
                </div>

                <div class="chartAndOther">
                    <div class="some_exercises">
                        <div class="col-sm-4 chartButton" style="background:transparent;">
                            <span></span>
                            <select class="form-control" id="change_chart_type">
                                <option value="column">Column</option>
                                <option value="bar">Bar</option>
                                <option value="pie">Pie</option>
                                <option value="line">Line</option>
                                <option value="stacked-column">Stacked-Column</option>
                                <option value="stacked-bar">Stacked-Bar</option>
                            </select>
                        </div>
                    </div>

                    <div class="chartDownloadButtons">
                        <button class="btn btn-info" id="chartDownloadPdf">Save as Pdf</button>
                        <button class="btn btn-primary" id="chartDownloadPng">Save as Png</button>
                        <button class="btn btn-success" id="chartDownloadExcel">Save as Excel</button>
                    </div>


                    <div id="chartResult" style="min-width: 510px; height: 510px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
    </div>



    <script src="{{ asset('js/index.js') }}"></script>

    <script>
        $(document).ready(function () {
            main("{{$implodedIsoCodes}}", "{{$isoCodesCountriesEncoded}}");


        });
    </script>
@endsection
