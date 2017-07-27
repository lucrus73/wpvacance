<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WPV_Calendario
 *
 * @author lucio
 */
class WPV_Calendar
{
  private static $endpoint = 'getCalendarMarkup';
  private static $wrapperclass = 'wpv-calendar-wrapper';
  private static $previousMonthButton = 'wpv-calendar-previous-month-button';
  private static $nextMonthButton = 'wpv-calendar-next-month-button';
  
  
  function __construct()
  {
    Wpvacance::$instance->registerScriptParamsCallback(array($this, "bookingData"));
    Wpvacance::$instance->registerScriptParamsCallback(array($this, "load"));
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
  }

  public function registerRoutes()
  {    
    register_rest_route(WPV_BookingForm::$namespace, '/'.self::$endpoint, array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'get_calendar_markup'),
      ) );
  }
  
  public function get_calendar_markup(WP_REST_Request $request)
  {
    $offset = $request->get_param("offset");
    $span = $request->get_param("span");

    if (empty($offset) || empty($span) || intval($span) >= 3 || intval($span) < 1)
    {
      $offset = 0;
      $span = 2;
    }
    
    $result["markup"] = $this->months(intval($offset), intval($span));
    
    return $result;
  }
  
  public function getCalendar()
  {
    $html = '<div class="wpv-booking-option-title wpv-booking-startdate-title">'.__('When does your holiday start?', 'wpvacance').'</div>';
    $html .= '<div class="'.self::$wrapperclass.'">';
    $html .= '</div>';
    return $html;
  }

  public function months($offset = 0, $span = 2)
  {
    $ut_now = time(null);
    $m = date("m", $ut_now) + $offset;
    $utnow_day = (int)($ut_now / 86400);
    $html = '';
    $meseiniziale = $m;
    $mesefinale = $m + ($span - 1);
    for ($meseincostruzione = $meseiniziale; $meseincostruzione <= $mesefinale; $meseincostruzione++)
    {
      $adj = "";
      $d = date("d");
      $y = date("Y");
      $nd = date('t', mktime(0, 0, 0, $meseincostruzione, 1, $y));
      $mn = date('n', mktime(0, 0, 0, $meseincostruzione, 1, $y));
      $yn = date('Y', mktime(0, 0, 0, $meseincostruzione, 1, $y));
      $j = date('w', mktime(0, 0, 0, $meseincostruzione, 1, $y)) + 1;
      if ($j == "7")
      {
        $j = "0";
      }
      $MONTHS = array(1 =>  __('Jan', 'wpvacance'), 
                            __('Feb', 'wpvacance'), 
                            __('Mar', 'wpvacance'), 
                            __('Apr', 'wpvacance'),
                            __('May', 'wpvacance'),
                            __('Jun', 'wpvacance'),
                            __('Jul', 'wpvacance'),
                            __('Aug', 'wpvacance'),
                            __('Sep', 'wpvacance'),
                            __('Oct', 'wpvacance'),
                            __('Nov', 'wpvacance'),
                            __('Dec', 'wpvacance'));
      for ($k = 1; $k <= $j; $k++)
      {
        $adj .= '<td class="wpv-calendar-day wpv-calendar-day-disabled"> </td>';
      }

      $html .= '<div class="wpv-calendar-month">';
      $html .= '<table cellspacing="0" cellpadding="5" align="center" width="100" border="1">';
      $html .= '<thead>';
      $html .= '<tr>';
      $html .= '<th>';
      
      if ($meseincostruzione == $meseiniziale)
        $html .= '<i class="fa fa-arrow-left '.self::$previousMonthButton.'" data-wpvoffset="'.($offset - 1).'" data-wpvspan="'.$span.'" aria-hidden="true"></i>';
      
      $html .= '</th>';
      $html .= '<th colspan="5">';
      $html .= $MONTHS[$mn] . " " . $yn;
      $html .= '</th>';
      $html .= '<th align="center">';
      
      if ($meseincostruzione == $mesefinale)
        $html .= '<i class="fa fa-arrow-right '.self::$nextMonthButton.'" data-wpvoffset="'.($offset + 1).'" data-wpvspan="'.$span.'" aria-hidden="true"></i>';
      
      $html .= '</th>';
      $html .= '</tr>';
      $html .= '<tr>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Saturday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Sunday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Monday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Tuesday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Wednesday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Thursday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Friday', 'wpvacance')).'</th>';
      $html .= '</tr>';
      $html .= '</thead>';
      $html .= '<tbody>';
      $html .= '<tr>';
      for ($giornodelmese = 1; $giornodelmese <= $nd; $giornodelmese++)
      {
        $ut_dayofmonth = (int)(mktime(0, 0, 0, $meseincostruzione, $giornodelmese, $y) / 86400);
        $timeline_class = "wpv-calendar-day-today";
        if ($ut_dayofmonth < $utnow_day)
          $timeline_class = "wpv-calendar-day-inthepast";
        else
        if ($ut_dayofmonth > $utnow_day)
          $timeline_class = "wpv-calendar-day-inthefuture";
          
        $html .= $adj . '<td valign="top" data-wpvdayid="'.$ut_dayofmonth.'" class="wpv-calendar-day '.$timeline_class.'">' . $giornodelmese . '</td>';
        $adj = '';
        $j++;
        if ($j == 7)
        {
          $html .= '</tr><tr>';
          $j = 0;
        }
      }
      $html .= '</tr>';
      $html .= '</tbody>';
      $html .= '</table>';
      $html .= '</div>';
    }

    return $html; //ob_get_flush();
  }
  
  public function bookingData()
  {
    return array('click', 
                  'updateBookingAvailabilityFromCalendarClick', 
                  array('wpv-calendar-day'));
    
  }
  
  public function load()
  {
    return array('load', 
                  'loadCalendar', 
                  array('', // dummy value, used only when loading on button click, while here is onLoad
                        get_rest_url(),
                        WPV_BookingForm::$namespace, 
                        self::$wrapperclass,
                        0, // offset 0 = current month
                        2,  // span 2 months (current and next one)
                        self::$previousMonthButton,
                        self::$nextMonthButton
                      ));    
  }
  
  private function fc($string) // First Character
  {
    return strtoupper(substr($string, 0, 1));
  }

}
