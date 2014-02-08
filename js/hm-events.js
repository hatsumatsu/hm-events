jQuery( document ).ready( function() {

  var dateFormat = jQuery( '#hm-events_dateformat' ).val();
  var timeFormat = jQuery( '#hm-events_timeformat' ).val();

  /* 
  convert php date format string from WP settings to jquery ui date format string
  http://php.net/manual/de/function.date.php
  http://api.jqueryui.com/1.8/datepicker/
  http://trentrichardson.com/examples/timepicker/
  */

  if( dateFormat && timeFormat ) {

    dateFormat = dateFormat
      .replace( /d/, 'dd' )       /* day with leading zero */
      .replace( /j/, 'd' )        /* day no leading zero */
      .replace( /z/, 'o' )        /* day of year no leading zero */
      .replace( /D/, 'D' )        /* day name short */
      .replace( /l/, 'DD' )       /* day name long */
      .replace( /m/, 'mm' )       /* month with leading zero */
      .replace( /n/, 'm' )        /* month no leading zero */
      .replace( /M/, 'M' )        /* month name short */
      .replace( /F/, 'MM' )       /* month name long */
      .replace( /y/, 'y' )        /* year two digits */
      .replace( /Y/, 'yy' )       /* year four digits */
      .replace( /@/, 'U' );       /* UNIX timestamp */

    timeFormat = timeFormat
        .replace( /H/, 'HH' )   /* 24 hour with leading zero */
        .replace( /G/, 'H' )    /* 24 hour without leading zero */
        .replace( /h/, 'hh' )   /* 12 hour with leading zero */
        .replace( /g/, 'h' )    /* 12 hour without leading zero */
        .replace( /i/, 'mm' )   /* minute with leading zero */
        .replace( /s/, 'ss' )   /* second with leading zero */
        .replace( /a/, 'tt' )   /* a/p with leading zero */
        .replace( /A/, 'TT' )   /* am/pm */
        .replace( /O/, 'z' )    /* timezone */


    jQuery( '#hm-events_date' ).datetimepicker( {
      dateFormat: dateFormat,
      timeFormat: timeFormat
      } );

  }

});