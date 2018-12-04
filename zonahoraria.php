<?php
    function zonaHoraria($formato){
        //VARIABLE PARA EL HORARIO
        //$config=sql("SELECT * FROM configuracion WHERE nombre='config_zona_horaria'",SQL_SIMPLE);
        $horas = 7;
        // SELECT TIME ZONE
        $sign = "-"; // Whichever direction from GMT to your timezone.
        $h = $horas; // Hour for time zone goes here e.g. +8 or -4, just remove the + or -
        $dst = "true"; // Just insert "true" if your location uses daylight savings time or "false" if it does not
        
        // DETECT AND ADJUST FOR DAYLIGHT SAVINGS TIME
        if ($dst) {
            $daylight_saving = date('I');
            if ($daylight_saving){
                if ($sign == "-"){ $h=$h-1;  }
                else { $h=$h+1; }
            }
        }
        
        // FIND DIFFERENCE FROM GMT
        $hm = $h * 60;
        $ms = $hm * 60;
        
        // SET CURRENT TIME
        if ($sign == "-"){ $timestamp = time()-($ms); }
        else { $timestamp = time()+($ms); }
        
        // SAMPLE OUTPUT
        $gmdate = gmdate($formato, $timestamp);
        return $gmdate;
	}
?>