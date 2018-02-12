<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SebastianBergmann\GlobalState\TestFixture\SnapshotDomDocument;
use Sunra\PhpSimple\HtmlDomParser;
use App\models\Currencies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\Http\Requests\Item\Create;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;


class CurrenciesController extends Controller
{
    /*some properties*/

    public $urlISOCodes = "https://www.cba.am/am/SitePages/ExchangeArchive.aspx";

    public $urlLastMonth = "http://api.cba.am/exchangerates.asmx?op=ExchangeRatesLatest";

    protected $dom;

    public $isoCodes = array();

    public $isoCodesClear = array();

    public $implodedIsoCodes = "";

    public $isoCodesCountries = array();

    public $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );

    /*
     *  No params, nothing returns
     *  Magic method __construct
     *  Automatic called when object creates
     */
    public function __construct()
    {
        $dom = HtmlDomParser::file_get_html($this->urlISOCodes, false, stream_context_create($this->arrContextOptions));

        $this->dom = $dom;

        $this->getIsoCodesOnly();

        $this->getImplodedIsoCodes();

        $this->getIsoCodesWithCountries();
    }

    /*
     * Main Index function
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
//        $customFilterDateFrom = date('Y-m-d', time() - 2 * 86400);
//
//        $customFilterDateTo = date("Y-m-d", strtotime('tomorrow'));

        $implodedIsoCodes = $this->getImplodedIsoCodes();


        $isoCodesCountries = $this->isoCodesCountries;

        $isoCodesCountriesEncoded = json_encode($isoCodesCountries, JSON_UNESCAPED_UNICODE);

        return view('index', compact('implodedIsoCodes', 'isoCodesCountriesEncoded', 'isoCodesCountries'));
    }

    /*
     * Get All Iso Codes With Countries
     * @return $isoCodesCountries
     */
    public function getIsoCodesWithCountries()
    {
        $dom = $this->dom;

        $isoCodesCountries = array();

        foreach ($dom->find('.table_45 .gray_td') as $e) {
            $isoCodesCountries[] = array("isoCode" => $e->children[0]->innertext(), "isoCodeCountry" => $e->children[1]->innertext());
        }

        $this->isoCodesCountries = $isoCodesCountries;/* Change isoCodesCountries property*/

        return $isoCodesCountries;

    }

    /*
     * Get All Iso Codes
     * @return $isoCodes
     */
    public function getIsoCodesOnly()
    {
        $dom = $this->dom;

        $isoCodes = array();

        foreach ($dom->find('#ctl00_PlaceHolderMain_g_d6ca9d7a_5234_4ce8_87c4_a6f88cccb8e1_updatePanelctl00_PlaceHolderMain_g_d6ca9d7a_5234_4ce8_87c4_a6f88cccb8e1 table tr') as $e) {
            if ($e->children[0]->innertext() != "ISO(code)") {

                $isoCode = $e->children[0]->children[0]->innertext();

                $isoCodes[] = array("isoCode" => $isoCode);

            }
        }

        $this->isoCodes = $isoCodes; /* Change isoCodes property*/

        return $isoCodes;
    }


    /*
    * Get All Iso Codes Imploded
    * @return $implodedIsoCodes
    */
    public function getImplodedIsoCodes()
    {
        $isoCodesClear = array();

        foreach ($this->isoCodes as $isoCodeCurr) {
            $isoCodesClear[] = $isoCodeCurr["isoCode"];
        }
        $this->isoCodesClear = $isoCodesClear;

        $implodedIsoCodes = implode(",", $isoCodesClear);

        return $implodedIsoCodes;
    }

    /*
    * Get All Iso Codes Imploded
    * @param  \Illuminate\Http\Request $request
    * @return json  encoded  $finalDataResponse
    */
    public function getFiltertDatesIsoData(Request $request)
    {
        $customFilterDateFrom = $request->dateFrom;

        $customFilterDateTo = $request->dateTo;

        $implodedIsoCodes = $request->implodedIsoCodes;

        $finalDataResponse = array();

        $this->implodedIsoCodes = $implodedIsoCodes;

        $isoCodesClear = $this->isoCodesClear;

        $implodedIsoCodesUrl = "https://www.cba.am/am/SitePages/ExchangeArchive.aspx?DateFrom=" . $customFilterDateFrom . "&DateTo=" . $customFilterDateTo . "&ISOCodes=" . $implodedIsoCodes;

        $dom = HtmlDomParser::file_get_html($implodedIsoCodesUrl, false, stream_context_create($this->arrContextOptions));

        foreach ($dom->find('.table_46 >.gray_td') as $elements) {
            $currDate = $elements->childNodes()[0]->innertext();

            $currDateIsoValues = array();

            $childNodeIndex = 0;

            foreach ($elements->childNodes() as $element) {

                if ($childNodeIndex <= 44 && $childNodeIndex > 0) {
                    $currIsoCode = $isoCodesClear[$childNodeIndex - 1];

                    $currDateIsoValues[] = array("$currIsoCode" => $element->innertext());
                }

                $childNodeIndex++;
            }
            $finalDataResponse[$currDate] = $currDateIsoValues;
        }


        foreach ($dom->find('.table_46 >.gray_td_light') as $elements) {
            $currDate = $elements->childNodes()[0]->innertext();

            $currDateIsoValues = array();

            $childNodeIndexSecond = 0;

            foreach ($elements->children() as $element) {

                if ($childNodeIndexSecond <= 44 && $childNodeIndexSecond > 0) {
                    $currIsoCode = @$isoCodesClear[@$childNodeIndexSecond -1];

                    $currDateIsoValues[] = array("$currIsoCode" => $element->innertext());
                }


                $childNodeIndexSecond++;
            }
            $finalDataResponse[$currDate] = $currDateIsoValues;
        }


        return json_encode($finalDataResponse);
    }


}
