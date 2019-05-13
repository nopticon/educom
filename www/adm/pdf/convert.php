<?php

class convert {
    var $number;

    function __construct() {
        $this->number = 0.00;
    }

    function reset() {
        $this->number = 0.00;
    }

    function cv($numero) {
        $this->number = $numero;

        if ($this->number < 1000000000) {
            if ($this->number >= 1000000 && $this->number <= 999999999.99) {
                $num_letras = $this->millon() . $this->cien_mil() . $this->cien();
            } else if ($this->number >= 1000 && $this->number <= 999999.99) {
                $num_letras = $this->cien_mil() . $this->cien();
            } else if ($this->number >= 1 && $this->number <= 999.99) {
                $num_letras = $this->cien();
            } else if ($this->number >= 0.01 && $this->number <= 0.99) {
                if ($this->number == 0.01) {
                    $num_letras = '01/100';
                } else {
                    $num_letras = $this->cv(($this->number * 100) . '/100') . '/100';
                }
            }
        }

        return $num_letras;
    }

    function _zero($number) {
        if ($number < 10) {
            $number = '0' . $number;
        }
        return $number;
    }

    function centimos() {
        $this->number = number_format($this->number, 2, '.', '') * 100;

        /*$num_letra = ' Quetzales ';

        if ($this->number > 0) {
            $num_letra .= 'con ' . $this->_zero($this->number) . '/100';
        } else {
            $num_letra .= 'exactos';
        }*/

        return '';
    }

    function unidad($numero) {
        switch ($numero) {
            case 9:
                $num = 'nueve';
                break;
            case 8:
                $num = 'ocho';
                break;
            case 7:
                $num = 'siete';
                break;
            case 6:
                $num = 'seis';
                break;
            case 5:
                $num = 'cinco';
                break;
            case 4:
                $num = 'cuatro';
                break;
            case 3:
                $num = 'tres';
                break;
            case 2:
                $num = 'dos';
                break;
            case 1:
                $num = 'uno';
                break;
        }

        return $num;
    }

    function add_unidad($numero, $number, &$num_letra, $call_method = 'unidad', $add_and = true) {
        if ($numero > $number) {
            $num_letra .= (($add_and) ? 'y ' : '') . $this->$call_method($numero - $number);
        }
        return true;
    }

    function decena($numero) {
        if ($numero >= 90 && $numero <= 99) {
            $num_letra = 'noventa ';
            $this->add_unidad($numero, 90, $num_letra);
        } else if ($numero >= 80 && $numero <= 89) {
            $num_letra = 'ochenta ';
            $this->add_unidad($numero, 80, $num_letra);
        } else if ($numero >= 70 && $numero <= 79) {
            $num_letra = 'setenta ';
            $this->add_unidad($numero, 70, $num_letra);
        } else if ($numero >= 60 && $numero <= 69) {
            $num_letra = 'sesenta ';
            $this->add_unidad($numero, 60, $num_letra);
        } else if ($numero >= 50 && $numero <= 59) {
            $num_letra = 'cincuenta ';
            $this->add_unidad($numero, 50, $num_letra);
        } else if ($numero >= 40 && $numero <= 49) {
            $num_letra = 'cuarenta ';
            $this->add_unidad($numero, 40, $num_letra);
        } else if ($numero >= 30 && $numero <= 39) {
            $num_letra = 'treinta ';
            $this->add_unidad($numero, 30, $num_letra);
        } else if ($numero >= 20 && $numero <= 29) {
            if ($numero == 20) {
                $num_letra = 'veinte ';
            } else {
                $num_letra = 'veinti' . $this->unidad($numero - 20);
            }
        } else if ($numero >= 10 && $numero <= 19) {
            switch ($numero) {
                case 10:
                    $num_letra = 'diez ';
                    break;
                case 11:
                    $num_letra = 'once ';
                    break;
                case 12:
                    $num_letra = 'doce ';
                    break;
                case 13:
                    $num_letra = 'trece ';
                    break;
                case 14:
                    $num_letra = 'catorce ';
                    break;
                case 15:
                    $num_letra = 'quince ';
                    break;
                case 16:
                    $num_letra = 'dieciseis ';
                    break;
                case 17:
                    $num_letra = 'diecisiete ';
                    break;
                case 18:
                    $num_letra = 'dieciocho ';
                    break;
                case 19:
                    $num_letra = 'diecinueve ';
                    break;
            }
        } else {
            $num_letra = $this->unidad($numero);
        }

        return $num_letra;
    }

    function centena($numero) {
        if ($numero < 100) {
            return $this->decena($numero);
        }

        if ($numero >= 900 & $numero <= 999) {
            $num_letra = 'novecientos ';
            $this->add_unidad($numero, 900, $num_letra, 'decena', false);
        } else if ($numero >= 800 && $numero <= 899) {
            $num_letra = 'ochocientos ';
            $this->add_unidad($numero, 800, $num_letra, 'decena', false);
        } else if ($numero >= 700 && $numero <= 799) {
            $num_letra = 'setecientos ';
            $this->add_unidad($numero, 700, $num_letra, 'decena', false);
        } else if ($numero >= 600 && $numero <= 699) {
            $num_letra = 'seiscientos ';
            $this->add_unidad($numero, 600, $num_letra, 'decena', false);
        } else if ($numero >= 500 && $numero <= 599) {
            $num_letra = 'quinientos ';
            $this->add_unidad($numero, 500, $num_letra, 'decena', false);
        } else if ($numero >= 400 && $numero <= 499) {
            $num_letra = 'cuatrocientos ';
            $this->add_unidad($numero, 400, $num_letra, 'decena', false);
        } else if ($numero >= 300 && $numero <= 399) {
            $num_letra = 'trescientos ';
            $this->add_unidad($numero, 300, $num_letra, 'decena', false);
        } else if ($numero >= 200 && $numero <= 299) {
            $num_letra = 'doscientos ';
            $this->add_unidad($numero, 200, $num_letra, 'decena', false);
        } else if ($numero >= 100 && $numero <= 199) {
            if ($numero == 100) {
                $num_letra = 'cien ';
            } else {
                $num_letra = 'ciento ' . $this->decena($numero - 100);
            }
        }

        return $num_letra;
    }

    function cien() {
        $parcial = $car = 0;

        while (substr($this->number, 0, 1) == 0) {
            $this->number = substr($this->number, 1, strlen($this->number) - 1);
        }

        if ($this->number >= 1 && $this->number <= 9.99) {
            $car = 1;
        } else if ($this->number >= 10 && $this->number <= 99.99) {
            $car = 2;
        } else if ($this->number >= 100 && $this->number <= 999.99) {
            $car = 3;
        }

        $parcial = substr($this->number, 0, $car);
        $this->number = substr($this->number, $car);

        $num_letra = $this->centena($parcial) . $this->centimos();

        return $num_letra;
    }

    function cien_mil() {
        $parcial = 0; $car = 0;

        while (substr($this->number, 0, 1) == 0) {
            $this->number = substr($this->number, 1, strlen($this->number) - 1);
        }

        if ($this->number >= 1000 && $this->number <= 9999.99) {
            $car = 1;
        } else if ($this->number >= 10000 && $this->number <= 99999.99) {
            $car = 2;
        } else if ($this->number >= 100000 && $this->number <= 999999.99) {
            $car = 3;
        }

        $parcial = substr($this->number, 0, $car);
        $this->number = substr($this->number, $car);

        if ($parcial > 0) {
            if ($parcial == 1) {
                $num_letra = 'mil ';
            } else {
                $num_letra = $this->centena($parcial) . ' mil ';
            }
        }

        return $num_letra;
    }

    function millon() {
        $parcial = 0; $car = 0;

        while (substr($this->number, 0, 1) == 0) {
            $this->number = substr($this->number, 1, strlen($this->number) - 1);
        }

        if ($this->number >= 1000000 && $this->number <= 9999999.99) {
            $car = 1;
        } else if ($this->number >= 10000000 && $this->number <= 99999999.99) {
            $car = 2;
        } else if ($this->number >= 100000000 && $this->number <= 999999999.99) {
            $car = 3;
        }

        $parcial = substr($this->number, 0, $car);
        $this->number = substr($this->number, $car);

        if ($parcial == 1) {
            $num_letras = 'un millon ';
        } else {
            $num_letras = $this->centena($parcial) . ' millones ';
        }

        return $num_letras;
    }
}
