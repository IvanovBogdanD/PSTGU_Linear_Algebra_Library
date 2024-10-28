<?php

namespace LinearAlgebraPlex;
/**The library is distributed free of charge under a free license. Authors: Беликов Тихон Андреевич - tiKhon.belikov@yandex.ru, Иванов Богдан Дмитриевич - bogdanmikado@yandex.ru
   */
class Exception extends \Exception
{
}

class ComplexNumber
{
    private $real;
    private $imaginary;
    
    public function __construct($real, $imaginary)
    {
        $this->real = $real;
        $this->imaginary = $imaginary;
    }
    
    public function __toString()
    {
        return "{$this->real} + {$this->imaginary}i";
    }
    
    public function getReal()
    {
        return $this->real;
    }
    
    public function getImaginary()
    {
        return $this->imaginary;
    }
    
    // Получение модуля комплексного числа
    public function magnitude()
    {
        return sqrt(pow($this->real, 2) + pow($this->imaginary, 2));
    }
    
    // Альтернативное название для модуля
    public function abs()
    {
        return $this->magnitude();
    }
    
    // Получение аргумента комплексного числа в радианах
    public function argument()
    {
        return atan2($this->imaginary, $this->real);
    }
    
    // Получение сопряженного комплексного числа
    public function conjugate()
    {
        return new ComplexNumber($this->real, -$this->imaginary);
    }
    
    // Возведение в степень по формуле Муавра
    public function power($n)
    {
        $r = $this->magnitude();
        $phi = $this->argument();
        
        $newR = pow($r, $n);
        $newPhi = $n * $phi;
        
        $newReal = $newR * cos($newPhi);
        $newImaginary = $newR * sin($newPhi);
        
        return new ComplexNumber($newReal, $newImaginary);
    }
    
    // Получение тригонометрической формы в виде строки
    public function toTrigonometric()
    {
        $r = $this->magnitude();
        $phi = $this->argument();
        $phiDegrees = rad2deg($phi);
        return sprintf("%.2f(cos(%.2f°) + i*sin(%.2f°))", $r, $phiDegrees, $phiDegrees);
    }
    
    // Создание комплексного числа из тригонометрической формы
    public static function fromTrigonometric($r, $phiDegrees)
    {
        $phi = deg2rad($phiDegrees);
        $real = $r * cos($phi);
        $imaginary = $r * sin($phi);
        return new ComplexNumber($real, $imaginary);
    }
    
    public function add(ComplexNumber $other)
    {
        $real = $this->real + $other->real;
        $imaginary = $this->imaginary + $other->imaginary;
        return new ComplexNumber($real, $imaginary);
    }
    
    public function subtract(ComplexNumber $other)
    {
        $real = $this->real - $other->real;
        $imaginary = $this->imaginary - $other->imaginary;
        return new ComplexNumber($real, $imaginary);
    }
    
    public function multiply(ComplexNumber $other)
    {
        $real = $this->real * $other->real - $this->imaginary * $other->imaginary;
        $imaginary = $this->real * $other->imaginary + $this->imaginary * $other->real;
        return new ComplexNumber($real, $imaginary);
    }
    
    public function divide(ComplexNumber $other)
    {
        $denominator = pow($other->real, 2) + pow($other->imaginary, 2);
        $real = ($this->real * $other->real + $this->imaginary * $other->imaginary) / $denominator;
        $imaginary = ($this->imaginary * $other->real - $this->real * $other->imaginary) / $denominator;
        return new ComplexNumber($real, $imaginary);
    }
}

class NArray
{
    protected $data;
    protected $dimensions;

    public function __construct(array $data = null, array $dimensions = null, $fillType = 'zero', $isImaginaryInt = false)
    {
        if ($data !== null) {
            $this->data = $this->parseData($data);
            $this->dimensions = $this->getDimensions($this->data);
        } elseif ($dimensions !== null) {
            $this->dimensions = $dimensions;
            $this->data = $this->initializeArray($this->dimensions, $fillType, $isImaginaryInt);
        } else {
            throw new \InvalidArgumentException("Необходимо указать либо данные, либо размерности массива.");
        }
    }

    private function initializeArray($dimensions, $fillType = 'zero', $isImaginaryInt = false)
    {
        if (empty($dimensions)) {
            return [];
        }

        $size = array_shift($dimensions);
        $result = [];

        for ($i = 0; $i < $size; $i++) {
            if (empty($dimensions)) {
                switch ($fillType) {
                    case 'zero':
                        $result[] = 0;
                        break;
                    case 'random':
                        $result[] = mt_rand() / mt_getrandmax() * 2 - 1;
                        break;
                    case 'random_complex':
                        $real = mt_rand() / mt_getrandmax() * 2 - 1;
                        $imaginary = ($isImaginaryInt ? mt_rand() : mt_rand() / mt_getrandmax() * 2 - 1);
                        $result[] = new ComplexNumber($real, $imaginary);
                        break;
                    case 'random_complex_int':
                        $real = mt_rand();
                        $imaginary = ($isImaginaryInt ? mt_rand() : mt_rand() / mt_getrandmax() * 2 - 1);
                        $result[] = new ComplexNumber($real, $imaginary);
                        break;
                }
            } else {
                $result[] = $this->initializeArray($dimensions, $fillType, $isImaginaryInt);
            }
        }

        return $result;
    }

    private function parseData(array $data)
    {
        $result = [];
        foreach ($data as $value) {
            if (is_array($value)) {
                $result[] = $this->parseData($value);
            } else {
                $result[] = $this->parseValue($value);
            }
        }
        return $result;
    }

    /**
 * Метод для разбора значения и преобразования его в комплексное число.
 *
 * @param mixed $value Значение для разбора.
 * @return mixed Вещественное или комплексное число.
 */
private function parseValue($value)
{
    // Если значение уже является комплексным числом
    if (is_object($value) && is_a($value, 'ComplexNumber')) {
        return $value;
    }
    
    // Преобразуем в строку для унификации обработки
    $value = (string)$value;
    
    // Проверяем формат "a:b"
    if (strpos($value, ':') !== false) {
        $parts = explode(':', $value);
        $real = (float)($parts[0] ?: 0);
        $imaginary = (float)($parts[1] ?: 0);
        return new ComplexNumber($real, $imaginary);
    }
    
    // Проверяем чисто мнимое число (bi или -bi)
    if (preg_match('/^([+-])?(\d*\.?\d*)?i$/', $value, $matches)) {
        $imaginary = 1;
        if (isset($matches[2]) && $matches[2] !== '') {
            $imaginary = (float)$matches[2];
        }
        if (isset($matches[1]) && $matches[1] === '-') {
            $imaginary = -$imaginary;
        }
        return new ComplexNumber(0, $imaginary);
    }
    
    // Проверяем стандартный формат записи (a+bi или a-bi)
    if (preg_match('/^(-?\d*\.?\d*)?(?:\s*([+-])\s*(\d*\.?\d*)?i)?$/', $value, $matches)) {
        $real = ($matches[1] !== '') ? (float)$matches[1] : 0;
        $imaginary = 0;
        if (isset($matches[2])) {
            $imaginary = 1;
            if (isset($matches[3]) && $matches[3] !== '') {
                $imaginary = (float)$matches[3];
            }
            if ($matches[2] === '-') {
                $imaginary = -$imaginary;
            }
        }
        return new ComplexNumber($real, $imaginary);
    }
    
    // Если не удалось распознать формат, возвращаем 0
    return 0.0;
}
    private function getDimensions(array $array)
    {
        if (empty($array)) {
            return [];
        }
        $dimensions = [count($array)];
        $firstElement = reset($array);
        if (is_array($firstElement)) {
            $dimensions = array_merge($dimensions, $this->getDimensions($firstElement));
        }
        return $dimensions;
    }

    public function getElement($indices, $asComplexNumber = false)
    {
        $element = $this->data;
        foreach ($indices as $index) {
            if (!isset($element[$index])) {
                throw new \OutOfBoundsException("Index $index is out of bounds");
            }
            $element = $element[$index];
        }
        return $asComplexNumber ? $this->ensureComplexNumber($element) : $element;
    }

    public function setElement($indices, $value, $asComplexNumber = false)
    {
        $pointer = &$this->data;
        $lastIndex = array_pop($indices);
        foreach ($indices as $index) {
            if (!isset($pointer[$index])) {
                $pointer[$index] = [];
            }
            $pointer = &$pointer[$index];
        }
        $pointer[$lastIndex] = $asComplexNumber ? $this->ensureComplexNumber($value) : $value;
    }

    private function ensureComplexNumber($value)
    {
        return is_a($value, 'LinearAlgebraPlex\ComplexNumber') ? $value : new ComplexNumber($value, 0);
    }

    public function display($indent = 0, $separator = ' ')
    {
        $this->printArray($this->data, $indent, $separator);
    }
    
    private function printArray($array, $indent = 0, $separator = ' ')
    {
        if (!is_array($array)) {
            if (is_object($array) && is_a($array, 'LinearAlgebraPlex\ComplexNumber')) {
                $real = $array->getReal();
                $imaginary = $array->getImaginary();
                
                // Начинаем формирование строки
                $output = str_repeat('  ', $indent);
                
                // Чисто мнимое число
                if ($real == 0 && $imaginary != 0) {
                    if ($imaginary == 1) {
                        $output .= "i ";
                    } elseif ($imaginary == -1) {
                        $output .= "-i ";
                    } elseif ($imaginary > 0) {
                        $output .= $imaginary . "i ";
                    } else {
                        $output .= "-" . abs($imaginary) . "i ";
                    }
                }
                // Чисто вещественное число
                elseif ($imaginary == 0) {
                    $output .= $real . " ";
                }
                // Комплексное число
                else {
                    if ($real != 0) {
                        $output .= $real;
                    }
                    if ($imaginary > 0) {
                        $output .= " + " . $imaginary . "i ";
                    } elseif ($imaginary < 0) {
                        $output .= " - " . abs($imaginary) . "i ";
                    } else {
                        $output .= "i ";
                    }
                }
                echo $output;
            } else {
                echo str_repeat('  ', $indent) . $array . " ";
            }
        } else {
            foreach ($array as $element) {
                $this->printArray($element, $indent + 1, $separator);
                if (!is_array($element)) {
                    echo $separator;
                }
            }
            if ($indent === 0) {
                echo "\n";
            }
        }
    }
   public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $this->parseData($data);
        $this->dimensions = $this->getDimensions($this->data);
    }
}

class Matrix extends NArray
{
    public function __construct($rows, $cols, $type = 'zero', $isComplex = false, $isPrime = false)
    {
        if ($rows <= 0 || $cols <= 0) {
            throw new Exception("Invalid matrix dimensions");
        }
        parent::__construct(null, [$rows, $cols], $type, $isComplex);
        if ($isPrime) {
            $this->fillWithPrimes();
        }
    }
    
    public function IsInvertible()
    {
        $det = $this->determinant();
        return $det !== 0;
    }

    private function fillWithPrimes()
    {
        for ($i = 0; $i < $this->dimensions[0]; $i++) {
            for ($j = 0; $j < $this->dimensions[1]; $j++) {
                $prime = $this->getNextPrime();
                $this->setElement([$i, $j], $prime);
            }
        }
    }

    private function getNextPrime()
    {
        static $primes = [2];
        static $nextPrime = 3;

        while (true) {
            $isPrime = true;
            $sqrt = sqrt($nextPrime);
            foreach ($primes as $prime) {
                if ($prime > $sqrt) {
                    break;
                }
                if ($nextPrime % $prime === 0) {
                    $isPrime = false;
                    break;
                }
            }
            if ($isPrime) {
                $primes[] = $nextPrime;
                $prime = $nextPrime;
                $nextPrime += 2;
                return $prime;
            }
            $nextPrime += 2;
        }
    }

    public function add(Matrix $other)
    {
        if ($this->dimensions != $other->dimensions) {
            throw new Exception("Matrices must have the same dimensions");
        }

        $result = new Matrix($this->dimensions[0], $this->dimensions[1]);
        for ($i = 0; $i < $this->dimensions[0]; $i++) {
            for ($j = 0; $j < $this->dimensions[1]; $j++) {
                $value1 = $this->getElement([$i, $j]);
                $value2 = $other->getElement([$i, $j]);

                if (is_a($value1, 'LinearAlgebraPlex\ComplexNumber') && is_a($value2, 'LinearAlgebraPlex\ComplexNumber')) {
                    $result->setElement([$i, $j], $value1->add($value2));
                } elseif (is_numeric($value1) && is_numeric($value2)) {
                    $result->setElement([$i, $j], $value1 + $value2);
                } else {
                    throw new Exception("Unsupported operation");
                }
            }
        }
        return $result;
    }
    public function subtract(Matrix $other)
{
    if ($this->dimensions != $other->dimensions) {
        throw new Exception("Matrices must have the same dimensions");
    }

    $result = new Matrix($this->dimensions[0], $this->dimensions[1]);
    for ($i = 0; $i < $this->dimensions[0]; $i++) {
        for ($j = 0; $j < $this->dimensions[1]; $j++) {
            $value1 = $this->getElement([$i, $j]);
            $value2 = $other->getElement([$i, $j]);

            if (is_a($value1, 'LinearAlgebraPlex\ComplexNumber') && 
                is_a($value2, 'LinearAlgebraPlex\ComplexNumber')) {
                $result->setElement([$i, $j], $value1->subtract($value2));
            } elseif (is_numeric($value1) && is_numeric($value2)) {
                $result->setElement([$i, $j], $value1 - $value2);
            } elseif (is_numeric($value1) && 
                     is_a($value2, 'LinearAlgebraPlex\ComplexNumber')) {
                $complexValue1 = new ComplexNumber($value1, 0);
                $result->setElement([$i, $j], $complexValue1->subtract($value2));
            } elseif (is_a($value1, 'LinearAlgebraPlex\ComplexNumber') && 
                     is_numeric($value2)) {
                $complexValue2 = new ComplexNumber($value2, 0);
                $result->setElement([$i, $j], $value1->subtract($complexValue2));
            } else {
                throw new Exception("Unsupported operation");
            }
        }
    }
    return $result;
}


    public function multiply(Matrix $other)
    {
        if ($this->dimensions[1] != $other->dimensions[0]) {
            throw new Exception("Matrices have invalid dimensions for multiplication");
        }

        foreach ($this->data as $row) {
            foreach ($row as $element) {
                if (!is_a($element, 'LinearAlgebraPlex\ComplexNumber') && !is_numeric($element)) {
                    throw new Exception("Unsupported operation");
                }
            }
        }

        foreach ($other->data as $row) {
            foreach ($row as $element) {
                if (!is_a($element, 'LinearAlgebraPlex\ComplexNumber') && !is_numeric($element)) {
                    throw new Exception("Unsupported operation");
                }
            }
        }

        $result = new Matrix($this->dimensions[0], $other->dimensions[1]);
        for ($i = 0; $i < $result->dimensions[0]; $i++) {
            for ($j = 0; $j < $result->dimensions[1]; $j++) {
                $sumScalar = 0;
                $sumComplex = null;
                for ($k = 0; $k < $this->dimensions[1]; $k++) {
                    $value1 = $this->getElement([$i, $k]);
                    $value2 = $other->getElement([$k, $j]);

                    if (is_a($value1, 'LinearAlgebraPlex\ComplexNumber') && is_a($value2, 'LinearAlgebraPlex\ComplexNumber')) {
                        $product = $value1->multiply($value2);
                    } elseif (is_numeric($value1) && is_numeric($value2)) {
                        $product = $value1 * $value2;
                    } elseif (is_a($value1, 'LinearAlgebraPlex\ComplexNumber') && is_numeric($value2)) {
                        $product = $value1->multiply(new ComplexNumber($value2, 0));
                    } elseif (is_numeric($value1) && is_a($value2, 'LinearAlgebraPlex\ComplexNumber')) {
                        $product = $value2->multiply(new ComplexNumber($value1, 0));
                    } else {
                        throw new Exception("Unsupported operation");
                    }

                    if (is_a($product, 'LinearAlgebraPlex\ComplexNumber')) {
                        if ($sumComplex === null) {
                            $sumComplex = $product;
                        } else {
                            $sumComplex = $sumComplex->add($product);
                        }
                    } else {
                        $sumScalar += $product;
                    }
                }
                if ($sumComplex !== null) {
                    $result->setElement([$i, $j], $sumComplex);
                } else {
                    $result->setElement([$i, $j], $sumScalar);
                }
            }
        }
        return $result;
    }
    public function multiplyByComplexNumber($complexNumber)
{
    if (!is_a($complexNumber, 'LinearAlgebraPlex\ComplexNumber')) {
        throw new Exception("Argument must be a ComplexNumber instance");
    }

    $result = new Matrix($this->dimensions[0], $this->dimensions[1], 'zero', true);
    for ($i = 0; $i < $this->dimensions[0]; $i++) {
        for ($j = 0; $j < $this->dimensions[1]; $j++) {
            $value = $this->getElement([$i, $j]);
            if (is_a($value, 'LinearAlgebraPlex\ComplexNumber')) {
                $result->setElement([$i, $j], $value->multiply($complexNumber));
            } elseif (is_numeric($value)) {
                $complexValue = new ComplexNumber($value, 0);
                $result->setElement([$i, $j], $complexValue->multiply($complexNumber));
            } else {
                throw new Exception("Unsupported element type in matrix");
            }
        }
    }
    return $result;
}

public function solveLinearSystem($b)
{
    $n = $this->dimensions[0];
    $matrix = $this->data;
    $x = array_fill(0, $n, new ComplexNumber(0, 0));

    for ($i = 0; $i < $n; $i++) {
        $maxRow = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if ($matrix[$j][$i]->abs() > $matrix[$maxRow][$i]->abs()) {
                $maxRow = $j;
            }
        }

        if ($matrix[$maxRow][$i]->abs() == 0) {
            throw new Exception("System has no unique solution");
        }

        if ($maxRow != $i) {
            $temp = $matrix[$i];
            $matrix[$i] = $matrix[$maxRow];
            $matrix[$maxRow] = $temp;
            $temp = $b[$i];
            $b[$i] = $b[$maxRow];
            $b[$maxRow] = $temp;
        }

        for ($j = $i + 1; $j < $n; $j++) {
            $factor = $matrix[$j][$i]->divide($matrix[$i][$i]);
            for ($k = $i + 1; $k < $n; $k++) {
                $matrix[$j][$k] = $matrix[$j][$k]->subtract($factor->multiply($matrix[$i][$k]));
            }
            $b[$j] = $b[$j]->subtract($factor->multiply($b[$i]));
        }
    }

    for ($i = $n - 1; $i >= 0; $i--) {
        $sum = new ComplexNumber(0, 0);
        for ($j = $i + 1; $j < $n; $j++) {
            $sum = $sum->add($matrix[$i][$j]->multiply($x[$j]));
        }
        $x[$i] = $b[$i]->subtract($sum)->divide($matrix[$i][$i]);
    }

    return $x;
}
    public function determinant($method = 'gaussian')
    {
        switch ($method) {
            case 'gaussian':
                return $this->gaussianDeterminant();
            case 'lu':
                return $this->luDeterminant();
            default:
                throw new Exception("Invalid determinant calculation method, I will do luDeterminant");
                
               
        }
    }


    private function gaussianDeterminant()
{
    $n = $this->dimensions[0];
    $matrix = $this->data;
    $det = new ComplexNumber(1, 0);  // Инициализируем определитель как комплексное число 1 + 0i

    for ($i = 0; $i < $n; $i++) {
        $maxRow = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if ($matrix[$j][$i]->abs() > $matrix[$maxRow][$i]->abs()) {
                $maxRow = $j;
            }
        }

        if ($matrix[$maxRow][$i]->abs() == 0) {
            return new ComplexNumber(0, 0);  // Матрица вырожденная, возвращаем 0 + 0i
        }

        if ($maxRow != $i) {
            $det = $det->multiply(new ComplexNumber(-1, 0));  // Меняем знак определителя
            $temp = $matrix[$i];
            $matrix[$i] = $matrix[$maxRow];
            $matrix[$maxRow] = $temp;
        }

        $det = $det->multiply($matrix[$i][$i]);

        for ($j = $i + 1; $j < $n; $j++) {
            $factor = $matrix[$j][$i]->divide($matrix[$i][$i]);
            for ($k = $i + 1; $k < $n; $k++) {
                $matrix[$j][$k] = $matrix[$j][$k]->subtract($factor->multiply($matrix[$i][$k]));
            }
        }
    }

    return $det;
}


private function luDeterminant()
{
    $n = $this->dimensions[0];
    $matrix = $this->data;
    $det = new ComplexNumber(1, 0);  // Начальное значение определителя

    for ($i = 0; $i < $n; $i++) {
        $maxRow = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if ($matrix[$j][$i]->abs() > $matrix[$maxRow][$i]->abs()) {
                $maxRow = $j;
            }
        }

        if ($matrix[$maxRow][$i]->abs() == 0) {
            return new ComplexNumber(0, 0);  // Матрица вырожденная
        }

        if ($maxRow != $i) {
            $det = $det->multiply(new ComplexNumber(-1, 0));  // Меняем знак определителя
            $temp = $matrix[$i];
            $matrix[$i] = $matrix[$maxRow];
            $matrix[$maxRow] = $temp;
        }

        $det = $det->multiply($matrix[$i][$i]);

        for ($j = $i + 1; $j < $n; $j++) {
            $factor = $matrix[$j][$i]->divide($matrix[$i][$i]);
            for ($k = $i + 1; $k < $n; $k++) {
                $matrix[$j][$k] = $matrix[$j][$k]->subtract($factor->multiply($matrix[$i][$k]));
            }
        }
    }

    return $det;
}



    

    public function renderMatrix()
    {
        $html = "<table>";
        for ($i = 0; $i < $this->dimensions[0]; $i++) {
            $html .= "<tr>";
            for ($j = 0; $j < $this->dimensions[1]; $j++) {
                $html .= "<td>" . $this->getElement([$i, $j]) . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }

    public function renderStyledMatrix($styles = [])
    {
        $defaultStyles = [
            'table' => 'border-collapse: collapse;',
            'td' => 'border: 1px solid black; padding: 5px;',
            'even-row' => 'background-color: #f2f2f2;',
            'odd-row' => 'background-color: #ffffff;',
        ];

        $styles = array_merge($defaultStyles, $styles);

        $html = "<table style='{$styles['table']}'>";
        for ($i = 0; $i < $this->dimensions[0]; $i++) {
            $rowStyle = ($i % 2 == 0) ? $styles['even-row'] : $styles['odd-row'];
            $html .= "<tr style='{$rowStyle}'>";
            for ($j = 0; $j < $this->dimensions[1]; $j++) {
                $html .= "<td style='{$styles['td']}'>" . $this->getElement([$i, $j]) . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }
    /**
     * Вычисляет обратную матрицу.
     *
     * @return Matrix|null Обратная матрица или null, если матрица вырожденная.
     */
    public function inverse()
{
	if ($this->IsInvertible() == false)
		{
			print("Матрица необратима");
			return null;
		}
	
    $n = $this->dimensions[0];
    $augmented = array_merge($this->data, array_fill(0, $n, array_fill(0, $n, 0)));
    for ($i = 0; $i < $n; $i++) {
        $augmented[$i] = array_merge($augmented[$i], [new ComplexNumber(1, 0)]);
    }

    for ($i = 0; $i < $n; $i++) {
        $maxRow = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if (is_a($augmented[$j][$i], 'ComplexNumber')) {
                $abs1 = $augmented[$j][$i]->abs();
            } elseif (is_numeric($augmented[$j][$i])) {
                $abs1 = abs($augmented[$j][$i]);
            } else {
                throw new Exception("Unsupported operation");
            }

            if (is_a($augmented[$maxRow][$i], 'ComplexNumber')) {
                $abs2 = $augmented[$maxRow][$i]->abs();
            } elseif (is_numeric($augmented[$maxRow][$i])) {
                $abs2 = abs($augmented[$maxRow][$i]);
            } else {
                throw new Exception("Unsupported operation");
            }

            if ($abs1 > $abs2) {
                $maxRow = $j;
            }
        }

        if (is_a($augmented[$maxRow][$i], 'ComplexNumber') && $augmented[$maxRow][$i]->abs() == 0) {
            return null; // Матрица вырожденная
        }

        if ($maxRow != $i) {
            $temp = $augmented[$i];
            $augmented[$i] = $augmented[$maxRow];
            $augmented[$maxRow] = $temp;
        }

        $augmented[$i] = array_map(function ($value) use ($augmented, $i) {
            if (is_a($value, 'ComplexNumber')) {
                return $value->divide($augmented[$i][$i]);
            } elseif (is_numeric($value)) {
                return $value / $augmented[$i][$i];
            } else {
                print("Unsupported operation");
            }
        }, $augmented[$i]);

        for ($j = 0; $j < $n; $j++) {
            if ($j != $i) {
                $factor = $augmented[$j][$i];
                $augmented[$j] = array_map(function ($value, $other) use ($factor, $i) {
                    if (is_a($value, 'ComplexNumber') && is_a($other, 'ComplexNumber')) {
                        return $value->subtract($factor->multiply($other));
                    } elseif (is_a($value, 'ComplexNumber') && is_numeric($other)) {
                        return $value->subtract(new ComplexNumber($factor->getReal() * $other, $factor->getImaginary() * $other));
                    } elseif (is_numeric($value) && is_a($other, 'ComplexNumber')) {
                        return $value - $factor * $other->getReal();
                    } elseif (is_numeric($value) && is_numeric($other)) {
                        return $value - $factor * $other;
                    } elseif (is_a($value, 'ComplexNumber') && is_a($factor, 'ComplexNumber') && is_numeric($other)) {
                        return $value->subtract($factor->multiply(new ComplexNumber($other, 0)));
                    } elseif (is_numeric($value) && is_a($factor, 'ComplexNumber') && is_a($other, 'ComplexNumber')) {
                        return $value - $factor->multiply($other)->getReal();
                    } else {
                        print("Unsupported operation");
                    }
                }, $augmented[$j], $augmented[$i]);
            }
        }
    }

    $inverse = array_map(function ($row) use ($n) {
        return array_slice($row, $n);
    }, $augmented);

    return $inverse;
}




    /**
     * Находит собственные векторы и собственные значения матрицы.
     *
     * @return array Массив, содержащий собственные значения и собственные векторы.
     */
    public function eigenvectors()
    {
        $n = $this->dimensions[0];
        $eigenvalues = [];
        $eigenvectors = [];

        for ($i = 0; $i < $n; $i++) {
            $matrix = $this->data;
            for ($j = 0; $j < $n; $j++) {
                if ($i != $j) {
                    $matrix[$j] = array_map(function ($value) use ($matrix, $i, $j) {
                        return $value->subtract($matrix[$i][$j]);
                    }, $matrix[$j]);
                }
            }

            $eigenvalue = $matrix[$i][$i];
            $eigenvector = array_fill(0, $n, new ComplexNumber(0, 0));
            $eigenvector[$i] = new ComplexNumber(1, 0);

            for ($j = 0; $j < $n; $j++) {
                if ($j != $i) {
                    $factor = $matrix[$j][$i]->divide($matrix[$i][$i]);
                    $eigenvector[$j] = $factor->negate();
                }
            }

            $eigenvalues[] = $eigenvalue;
            $eigenvectors[] = new Vector($eigenvector, true);
        }

        return ['eigenvalues' => $eigenvalues, 'eigenvectors' => $eigenvectors];
    }

    /**
     * Решает переопределенную систему линейных уравнений методом наименьших квадратов.
     *
     * @param Vector $b Вектор правых частей системы уравнений.
     * @return Vector Вектор решений системы уравнений.
     */
    public function leastSquares(Vector $b)
    {
        $A = $this;
        $At = $A->transpose();
        $AtA = $At->multiply($A);
        $Atb = $At->multiply($b);

        $x = $AtA->inverse()->multiply($Atb);

        return $x;
    }
    /**
 * Создает единичную матрицу заданного размера.
 *
 * @param int $size Размер матрицы.
 * @return Matrix Единичная матрица.
 */
private function createIdentityMatrix($size)
{
    $data = array_fill(0, $size, array_fill(0, $size, 0));
    for ($i = 0; $i < $size; $i++) {
        $data[$i][$i] = new ComplexNumber(1, 0);
    }
    return new Matrix($size, $size, $data);
}

/**
 * Возвращает размерности матрицы.
 *
 * @return array Размерности матрицы в формате [строки, столбцы].
 */
public function getDimensionsM()
{
    return $this->dimensions;
}

/**
 * Возвращает вектор-столбец матрицы по заданному индексу.
 *
 * @param int $index Индекс столбца.
 * @param int $startRow Начальная строка (по умолчанию 0).
 * @return Vector Вектор-столбец матрицы.
 */
private function getColumnVector($index, $startRow = 0)
{
    $vector = [];
    for ($i = $startRow; $i < $this->dimensions[0]; $i++) {
        $vector[] = $this->getElement([$i, $index]);
    }
    return new Vector($vector, true);
}

/**
 * Устанавливает вектор-столбец матрицы по заданному индексу.
 *
 * @param int $index Индекс столбца.
 * @param Vector $vector Вектор-столбец для установки.
 */
private function setColumnVector($index, Vector $vector)
{
    for ($i = 0; $i < $this->dimensions[0]; $i++) {
        $this->setElement([$i, $index], $vector->getElement([0, $i]));
    }
}
// Реализации алгоритмов LU-факторизации

    /**
     * Выполняет LU-факторизацию матрицы с использованием алгоритма Крауta.
     *
     * @param bool $partial Флаг, указывающий на необходимость выполнения частичной факторизации.
     * @return array Массив, содержащий матрицы L и U.
     */
    private function croutLU($partial = false)
    {
        $n = $this->dimensions[0];
        $L = $this->createIdentityMatrix($n);
        $U = clone $this;

        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i <= $j; $i++) {
                $sum = new ComplexNumber(0, 0);
                for ($k = 0; $k < $i; $k++) {
                    $sum = $sum->add($L->getElement([$i, $k])->multiply($U->getElement([$k, $j])));
                }
                $L->setElement([$i, $j], $U->getElement([$i, $j])->subtract($sum)->divide($U->getElement([$i, $i])));
            }

            for ($i = $j + 1; $i < $n; $i++) {
                $sum = new ComplexNumber(0, 0);
                for ($k = 0; $k < $j; $k++) {
                    $sum = $sum->add($L->getElement([$i, $k])->multiply($U->getElement([$k, $j])));
                }
                $U->setElement([$i, $j], $U->getElement([$i, $j])->subtract($sum));
            }
        }

        if ($partial) {
            $L->setData(array_slice($L->getData(), 0, $n));
            $U->setData(array_map(function ($row) {
                return array_slice($row, 0, count($row));
            }, $U->getData()));
        }

        return [$L, $U];
    }

    /**
     * Выполняет LU-факторизацию матрицы с использованием алгоритма Дулиттла.
     *
     * @param bool $partial Флаг, указывающий на необходимость выполнения частичной факторизации.
     * @return array Массив, содержащий матрицы L и U.
     */
    private function doolittleLU($partial = false)
    {
        $n = $this->dimensions[0];
        $L = $this->createIdentityMatrix($n);
        $U = clone $this;

        for ($j = 0; $j < $n; $j++) {
            for ($i = $j + 1; $i < $n; $i++) {
                $sum = new ComplexNumber(0, 0);
                for ($k = 0; $k < $j; $k++) {
                    $sum = $sum->add($L->getElement([$i, $k])->multiply($U->getElement([$k, $j])));
                }
                $L->setElement([$i, $j], $U->getElement([$i, $j])->subtract($sum)->divide($U->getElement([$j, $j])));
            }

            for ($i = $j + 1; $i < $n; $i++) {
                $sum = new ComplexNumber(0, 0);
                for ($k = 0; $k < $j; $k++) {
                    $sum = $sum->add($L->getElement([$j, $k])->multiply($U->getElement([$k, $i])));
                }
                $U->setElement([$j, $i], $U->getElement([$j, $i])->subtract($sum));
            }
        }

        if ($partial) {
            $L->setData(array_slice($L->getData(), 0, $n));
            $U->setData(array_map(function ($row) {
                return array_slice($row, 0, count($row));
            }, $U->getData()));
        }

        return [$L, $U];
    }

    /**
     * Выполняет LU-факторизацию матрицы с использованием алгоритма Холецкого.
     *
     * @return array Массив, содержащий матрицы L и U.
     * @throws Exception Если матрица не является положительно определенной.
     */
    private function choleskyLU()
    {
        $n = $this->dimensions[0];
        $L = $this->createIdentityMatrix($n);
        $U = clone $this;

        for ($j = 0; $j < $n; $j++) {
            $sum = new ComplexNumber(0, 0);
            for ($k = 0; $k < $j; $k++) {
                $sum = $sum->add($L->getElement([$j, $k])->multiply($U->getElement([$k, $j])));
            }
            $U->setElement([$j, $j], $U->getElement([$j, $j])->subtract($sum)->sqrt());

            if ($U->getElement([$j, $j])->getReal() < 0) {
                throw new Exception("Matrix is not positive definite");
            }

            for ($i = $j + 1; $i < $n; $i++) {
                $sum = new ComplexNumber(0, 0);
                for ($k = 0; $k < $j; $k++) {
                    $sum = $sum->add($L->getElement([$i, $k])->multiply($U->getElement([$k, $j])));
                }
                $L->setElement([$i, $j], $U->getElement([$i, $j])->subtract($sum)->divide($U->getElement([$j, $j])));
            }
        }

        return [$L, $U];
    }
    /**private function householderQR()
{
    $n = $this->dimensions[0];
    $m = $this->dimensions[1];
    $Q = $this->createIdentityMatrix($n);
    $R = clone $this;

    for ($j = 0; $j < min($n - 1, $m); $j++) {
        $x = $R->getColumnVector($j, $j);
        
        // Вычисляем норму вектора-столбца
        $normX = $x->norm();
        if ($normX == 0) continue;

        // Создаем знаковый множитель
        $sign = $x->getElement([0])->abs() > 0 ? 
                $x->getElement([0])->divide(new ComplexNumber($x->getElement([0])->abs(), 0)) :
                new ComplexNumber(1, 0);
                
        $u = $x->add(new Vector([new ComplexNumber($normX, 0)], true)->multiply($sign));
        $normU = $u->norm();
        
        if ($normU == 0) continue;
        
        // Нормализуем вектор u
        $v = $u->divide(new ComplexNumber($normU, 0));
        
        // Создаем матрицу отражения
        $P = $this->createIdentityMatrix($n - $j);
        $vvT = $this->createOuterProduct($v, $v);
        $P = $P->subtract($vvT->multiply(new ComplexNumber(2, 0)));
        
        // Применяем преобразование к подматрице R
        $subR = new Matrix($n - $j, $m - $j);
        for ($i = $j; $i < $n; $i++) {
            for ($k = $j; $k < $m; $k++) {
                $subR->setElement([$i - $j, $k - $j], $R->getElement([$i, $k]));
            }
        }
        
        $subR = $P->multiply($subR);
        
        // Копируем результат обратно в R
        for ($i = $j; $i < $n; $i++) {
            for ($k = $j; $k < $m; $k++) {
                $R->setElement([$i, $k], $subR->getElement([$i - $j, $k - $j]));
            }
        }
        
        // Обновляем Q
        $fullP = $this->createIdentityMatrix($n);
        for ($i = $j; $i < $n; $i++) {
            for ($k = $j; $k < $n; $k++) {
                $fullP->setElement([$i, $k], $P->getElement([$i - $j, $k - $j]));
            }
        }
        $Q = $Q->multiply($fullP);
    }

    return [$Q, $R];
}
*/
public function transpose()
{
    $n = $this->dimensions[0];
    $m = $this->dimensions[1];
    $transposed = new Matrix($m, $n);

    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $m; $j++) {
            $transposed->setElement([$j, $i], $this->getElement([$i, $j]));
        }
    }

    return $transposed;
}


private function createOuterProduct(Vector $u, Vector $v)
{
    $n = $u->getDimensionsM()[1];
    $m = $v->getDimensionsM()[1];
    $result = new Matrix($n, $m);

    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $m; $j++) {
            $result->setElement([$i, $j], $u->getElement([0, $i])->multiply($v->getElement([0, $j])));
        }
    }

    return $result;
}
/**
 * Выполняет QR-факторизацию матрицы с использованием алгоритма Гивенса.
 *
 * @return array Массив, содержащий матрицы Q и R.
 */
private function givensQR()
{
    $n = $this->dimensions[0];
    $m = $this->dimensions[1];
    $Q = $this->createIdentityMatrix($n);
    $R = clone $this;

    for ($j = 0; $j < min($n, $m); $j++) {
        for ($i = $m - 1; $i >= $j + 1; $i--) {
            $r = $R->getElement([$i - 1, $j]);
            $s = $R->getElement([$i, $j]);

            if (is_a($r, 'ComplexNumber')) {
                $rAbs = $r->abs();
            } elseif (is_numeric($r)) {
                $rAbs = abs($r);
            } else {
                throw new Exception("Unsupported operation");
            }

            if (is_a($s, 'ComplexNumber')) {
                $sAbs = $s->abs();
            } elseif (is_numeric($s)) {
                $sAbs = abs($s);
            } else {
                throw new Exception("Unsupported operation");
            }

            $norm = sqrt(pow($rAbs, 2) + pow($sAbs, 2));

            if ($norm !== 0) {
                $c = $r->divide(new ComplexNumber($norm, 0));
                $s = $s->divide(new ComplexNumber($norm, 0));

                $G = $this->createIdentityMatrix($n);
                $G->setElement([$i - 1, $i - 1], $c);
                $G->setElement([$i - 1, $i], $s->negate());
                $G->setElement([$i, $i - 1], $s);
                $G->setElement([$i, $i], $c);

                $R = $G->transpose()->multiply($R);
                $Q = $Q->multiply($G);
            }
        }
    }

    return [$Q, $R];
}

    
     private function gramSchmidtQR()
    {
        $n = $this->dimensions[0];
        $m = $this->dimensions[1];
        $Q = $this->createIdentityMatrix($n);
        $R = clone $this;

        for ($j = 0; $j < $m; $j++) {
            $v = $R->getColumnVector($j);
            if ($v->norm() == 0) {
                continue; // Пропускаем нулевые векторы
            }
            
            for ($i = 0; $i < $j; $i++) {
                $u = $Q->getColumnVector($i);
                $dotProduct = $u->dotProduct($v);
                $denominator = $u->dotProduct($u);
                
                if ($denominator->abs() > 0) {
                    $projection = $u->multiply($dotProduct->divide($denominator));
                    $v = $v->subtract($projection);
                }
            }
            
            $norm = $v->norm();
            if ($norm > 0) {
                $Q->setColumnVector($j, $v->divide(new ComplexNumber($norm, 0)));
            }
        }

        // Вычисляем R как Q^T * A
        $R = $Q->transpose()->multiply($this);

        return [$Q, $R];
    }
    
    /**
     * Выполняет LU-факторизацию матрицы с использованием выбранного алгоритма.
     *
     * @param string $algorithm Алгоритм для LU-факторизации ('crout', 'doolittle' или 'cholesky').
     * @param bool $partial Флаг, указывающий на необходимость выполнения частичной факторизации.
     * @return array Массив, содержащий матрицы L и U.
     */
    public function luFactorization($algorithm = 'crout', $partial = false)
    {
        switch ($algorithm) {
            case 'crout':
                return $this->croutLU($partial);
            case 'doolittle':
                return $this->doolittleLU($partial);
            case 'cholesky':
                return $this->choleskyLU();
            default:
                throw new Exception("Invalid LU factorization algorithm");
        }
    }

    /**
     * Выполняет QR-факторизацию матрицы с использованием выбранного алгоритма.
     *
     * @param string $algorithm Алгоритм для QR-факторизации ('gram-schmidt', 'householder' или 'givens').
     * @return array Массив, содержащий матрицы Q и R.
     */
    public function qrFactorization($algorithm = 'givensQR')
    {
        switch ($algorithm) {
            //case 'gram-schmidt':
            //    return $this->gramSchmidtQR();
            //case 'householder':
            //    return $this->householderQR();
            case 'givens':
                return $this->givensQR();
            default:
                throw new Exception("Invalid QR factorization algorithm");
        }
    }
    public function dotProduct(Vector $other)
{
    if ($this->dimensions != $other->dimensions) {
        throw new Exception("Vectors must have the same dimensions");
    }
    $result = new ComplexNumber(0, 0);
    for ($i = 0; $i < $this->dimensions[1]; $i++) {
        $result = $result->add($this->getElement([0, $i])->multiply($other->getElement([0, $i])));
    }
    return $result;
}
    
}
class Vector extends Matrix
{
    public function __construct($data, $isComplex = false)
    {
        parent::__construct(1, count($data), null, $isComplex);
        $this->data = [$data];
    }
    public function norm($p = 2)
    {
        if ($p == 2) {
            return sqrt($this->dotProduct($this)->getReal());
        } else {
            $sum = 0;
            foreach ($this->data[0] as $element) {
                $sum += pow($element->abs(), $p);
            }
            return pow($sum, 1 / $p);
        }
    }
    
    public function divide($divisor)
    {
        if (is_a($divisor, 'ComplexNumber')) {
            return new Vector(array_map(function ($element) use ($divisor) {
                return $element->divide($divisor);
            }, $this->data[0]));
        } elseif (is_numeric($divisor)) {
            return new Vector(array_map(function ($element) use ($divisor) {
                return $element->divide(new ComplexNumber($divisor, 0));
            }, $this->data[0]));
        } else {
            throw new Exception("Invalid divisor type");
        }
    }
    public function angleWithVector(Vector $other, $metric = 'euclidean')
    {
        switch ($metric) {
            case 'euclidean':
                return $this->euclideanAngle($other);
            case 'manhattan':
                return $this->manhattanAngle($other);
            case 'minkowski':
            //    return $this->minkowskiAngle($other);
            default:
                throw new Exception("Invalid angle metric");
        }
    }

    private function euclideanAngle(Vector $other)
    {
        $dot = $this->dotProduct($other);
        $norm1 = $this->norm();
        $norm2 = $other->norm();
        return acos($dot / ($norm1 * $norm2));
    }

    private function manhattanAngle(Vector $other)
    {
        if ($this->dimensions != $other->dimensions) {
            throw new Exception("Vectors must have the same dimensions");
        }

        $diff = array_map(function ($a, $b) {
            return $a->subtract($b)->abs();
        }, $this->data[0], $other->data[0]);

        $manhattanNorm = array_sum($diff);

        if ($manhattanNorm == 0) {
            return 0; // Угол между двумя одинаковыми векторами
        }

        $cosine = 0;
        for ($i = 0; $i < $this->dimensions[1]; $i++) {
            $cosine += min($this->getElement([0, $i])->abs(), $other->getElement([0, $i])->abs());
        }

        return acos($cosine / $manhattanNorm);
    }
/**
     * Вычисляет угол между векторами с использованием метрики Минковского
     * @param Vector $other Второй вектор
     * @param float $p Параметр метрики Минковского
     * @return float Угол между векторами
     * @throws Exception если размерности векторов не совпадают
     */
    private function minkowskiAngle(Vector $other, $p = 3)
    {
        if ($this->dimensions != $other->dimensions) {
            throw new Exception("Vectors must have the same dimensions");
        }

        $diff = array_map(function ($a, $b) use ($p) {
            return $a->subtract($b)->abs()->pow($p);
        }, $this->data[0], $other->data[0]);

        $minkowskiNorm = pow(array_sum($diff), 1 / $p);

        if ($minkowskiNorm == 0) {
            return 0;
        }

        $cosine = 0;
        for ($i = 0; $i < $this->dimensions[1]; $i++) {
            $cosine += min(
                $this->getElement([0, $i])->abs()->pow($p)->getReal(),
                $other->getElement([0, $i])->abs()->pow($p)->getReal()
            );
        }

        return acos($cosine / $minkowskiNorm);
    }




    public function visualize(Vector $other = null)
    {
        // Визуализация векторов и угла между ними (если другой вектор передан)
    }

    public function composeTransformations()
{
    $transformations = func_get_args();
    $result = function ($vector) use ($transformations) {
        $result = $vector;
        foreach ($transformations as $transformation) {
            $result = $transformation($result);
        }
        return $result;
    };
    return $result;
}


    /**
     * Находит ядро линейного преобразования
     * Ядро - это множество всех векторов, которые отображаются в нулевой вектор
     * @param callable $transformation Функция линейного преобразования
     * @return array Базисные векторы ядра преобразования
     */
    public function kernelOfTransformation($transformation)
    {
        // Применяем преобразование к единичным векторам базиса
        $basis = [];
        $nullVector = new Vector(array_fill(0, $this->dimensions[1], new ComplexNumber(0, 0)), true);
        
        // Создаем систему линейных уравнений
        $equations = [];
        $variables = [];
        
        // Генерируем базисные векторы
        for ($i = 0; $i < $this->dimensions[1]; $i++) {
            $basisVector = array_fill(0, $this->dimensions[1], new ComplexNumber(0, 0));
            $basisVector[$i] = new ComplexNumber(1, 0);
            $basis[] = new Vector($basisVector, true);
        }
        
        // Для каждого базисного вектора применяем преобразование
        foreach ($basis as $basisVector) {
            $transformed = $transformation($basisVector);
            if (!($transformed instanceof Vector)) {
                throw new Exception("Transformation must return a Vector");
            }
            
            // Добавляем уравнение T(v) = 0
            $equations[] = $transformed->data[0];
        }
        
        // Решаем систему линейных уравнений методом Гаусса
        $gaussianElimination = function($matrix) {
            $rows = count($matrix);
            $cols = count($matrix[0]);
            $rank = 0;
            
            for ($i = 0; $i < min($rows, $cols); $i++) {
                // Находим ведущий элемент
                $maxElement = $matrix[$i][$i]->abs();
                $maxRow = $i;
                
                for ($k = $i + 1; $k < $rows; $k++) {
                    if ($matrix[$k][$i]->abs() > $maxElement) {
                        $maxElement = $matrix[$k][$i]->abs();
                        $maxRow = $k;
                    }
                }
                
                if ($maxElement->getReal() < 1e-10) {
                    continue;
                }
                
                // Меняем строки местами
                if ($maxRow != $i) {
                    $temp = $matrix[$i];
                    $matrix[$i] = $matrix[$maxRow];
                    $matrix[$maxRow] = $temp;
                }
                
                // Обнуляем элементы под главной диагональю
                for ($k = $i + 1; $k < $rows; $k++) {
                    $c = $matrix[$k][$i]->divide($matrix[$i][$i]);
                    for ($j = $i; $j < $cols; $j++) {
                        $matrix[$k][$j] = $matrix[$k][$j]->subtract(
                            $matrix[$i][$j]->multiply($c)
                        );
                    }
                }
                $rank++;
            }
            
            return ['matrix' => $matrix, 'rank' => $rank];
        };
        
        $result = $gaussianElimination($equations);
        $nullSpace = [];
        
        // Находим базис ядра из приведенной матрицы
        $rank = $result['rank'];
        $dim = $this->dimensions[1];
        
        if ($rank < $dim) {
            // Для каждого свободного параметра создаем базисный вектор ядра
            for ($i = $rank; $i < $dim; $i++) {
                $vector = array_fill(0, $dim, new ComplexNumber(0, 0));
                $vector[$i] = new ComplexNumber(1, 0);
                
                // Находим остальные компоненты вектора
                for ($j = $rank - 1; $j >= 0; $j--) {
                    $sum = new ComplexNumber(0, 0);
                    for ($k = $j + 1; $k < $dim; $k++) {
                        $sum = $sum->add($result['matrix'][$j][$k]->multiply($vector[$k]));
                    }
                    $vector[$j] = $sum->multiply(new ComplexNumber(-1, 0))
                        ->divide($result['matrix'][$j][$j]);
                }
                
                $nullSpace[] = new Vector($vector, true);
            }
        }
        
        return $nullSpace;
    }

    /**
     * Находит образ линейного преобразования
     * Образ - это множество всех векторов, которые получаются в результате преобразования
     * @param callable $transformation Функция линейного преобразования
     * @return array Базисные векторы образа преобразования
     */
    public function imageOfTransformation($transformation)
    {
        // Применяем преобразование к базисным векторам
        $basis = [];
        $images = [];
        
        // Генерируем базисные векторы
        for ($i = 0; $i < $this->dimensions[1]; $i++) {
            $basisVector = array_fill(0, $this->dimensions[1], new ComplexNumber(0, 0));
            $basisVector[$i] = new ComplexNumber(1, 0);
            $basis[] = new Vector($basisVector, true);
        }
        
        // Для каждого базисного вектора применяем преобразование
        foreach ($basis as $basisVector) {
            $transformed = $transformation($basisVector);
            if (!($transformed instanceof Vector)) {
                throw new Exception("Transformation must return a Vector");
            }
            $images[] = $transformed;
        }
        
        // Находим линейно независимые векторы среди образов
        $independentVectors = [];
        foreach ($images as $vector) {
            $isIndependent = true;
            
            // Проверяем линейную независимость с уже найденными векторами
            if (!empty($independentVectors)) {
                $matrix = [];
                foreach ($independentVectors as $indVector) {
                    $matrix[] = $indVector->data[0];
                }
                $matrix[] = $vector->data[0];
                
                // Проверяем ранг матрицы
                $rank = $this->matrixRank($matrix);
                if ($rank <= count($independentVectors)) {
                    $isIndependent = false;
                }
            }
            
            if ($isIndependent) {
                $independentVectors[] = $vector;
            }
        }
        
        return $independentVectors;
    }

    /**
     * Вспомогательный метод для вычисления ранга матрицы
     */
    private function matrixRank($matrix)
    {
        $rows = count($matrix);
        $cols = count($matrix[0]);
        $rank = 0;
        $processed = array_fill(0, $rows, false);
        
        for ($j = 0; $j < $cols && $rank < $rows; $j++) {
            $i = 0;
            while ($i < $rows && ($processed[$i] || $matrix[$i][$j]->abs()->getReal() < 1e-10)) {
                $i++;
            }
            
            if ($i < $rows) {
                $rank++;
                $processed[$i] = true;
                for ($p = 0; $p < $rows; $p++) {
                    if ($p != $i && $matrix[$p][$j]->abs()->getReal() > 1e-10) {
                        $coef = $matrix[$p][$j]->divide($matrix[$i][$j]);
                        for ($q = 0; $q < $cols; $q++) {
                            $matrix[$p][$q] = $matrix[$p][$q]->subtract(
                                $matrix[$i][$q]->multiply($coef)
                            );
                        }
                    }
                }
            }
        }
        
        return $rank;
    }
}
