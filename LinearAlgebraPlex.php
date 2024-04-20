<?php

namespace LinearAlgebraPlex;

/**
 * Базовый класс для исключений в библиотеке.
 */
class Exception extends \Exception
{
    // Это стандартный класс исключений в PHP
    // Вы можете добавить дополнительную логику или методы по необходимости(или когда-нибудь это сделаем мы)
}
// Класс для комплексных чисел
class ComplexNumber
{
    private $real;
    private $imaginary;

    public function __construct($real, $imaginary)
    {
        $this->real = $real;
        $this->imaginary = $imaginary;
    }

    public function getReal()
    {
        return $this->real;
    }

    public function getImaginary()
    {
        return $this->imaginary;
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

// Базовый класс для N-мерных массивов
class NArray
{
    protected $data;
    protected $dimensions;

    /**
     * Конструктор для инициализации N-мерного массива.
     *
     * @param array|null $data Многомерный массив с данными для инициализации. Если не указано, массив будет создан с нулями.
     * @param int[] $dimensions Размерности массива в формате [размерность_1, размерность_2, ..., размерность_N]. Если не указано, размерности будут определены из $data.
     * @param string $fillType Способ заполнения массива: 'zero' (по умолчанию), 'random', 'random_complex' или 'random_complex_int'. Игнорируется, если $data указано.
     * @param bool $isImaginaryInt Флаг, указывающий, что мнимая часть комплексных чисел должна быть целочисленной (действует только при $fillType = 'random_complex' и $data не указано).
     */
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
    /**
 * Инициализирует N-мерный массив по заданным размерностям и способу заполнения.
 *
 * @param array $dimensions Размерности массива в формате [размерность_1, размерность_2, ..., размерность_N].
 * @param string $fillType Способ заполнения массива: 'zero' (по умолчанию), 'random', 'random_complex' или 'random_complex_int'.
 * @param bool $isImaginaryInt Флаг, указывающий, что мнимая часть комплексных чисел должна быть целочисленной (действует только при $fillType = 'random_complex').
 * @return array Инициализированный N-мерный массив.
 */
private function initializeArray($dimensions, $fillType = 'zero', $isImaginaryInt = false)
{
    if (empty($dimensions)) {
        return [];
    }

    $size = array_shift($dimensions);
    $result = [];

    for ($i = 0; $i < $size; $i++) {
        $result[] = $this->initializeArray($dimensions, $fillType, $isImaginaryInt);
    }

    if (empty($dimensions)) {
        switch ($fillType) {
            case 'zero':
                return $result;
            case 'random':
                return array_map(function () {
                    return mt_rand() / mt_getrandmax() * 2 - 1;
                }, $result);
            case 'random_complex':
                return array_map(function () use ($isImaginaryInt) {
                    $real = mt_rand() / mt_getrandmax() * 2 - 1;
                    $imaginary = ($isImaginaryInt ? mt_rand() : mt_rand() / mt_getrandmax() * 2 - 1);
                    return new ComplexNumber($real, $imaginary);
                }, $result);
            case 'random_complex_int':
                return array_map(function () use ($isImaginaryInt) {
                    $real = mt_rand();
                    $imaginary = ($isImaginaryInt ? mt_rand() : mt_rand() / mt_getrandmax() * 2 - 1);
                    return new ComplexNumber($real, $imaginary);
                }, $result);
        }
    }

    return $result;
}

    /**
     * Рекурсивный метод для разбора входных данных и преобразования их в многомерный массив.
     *
     * @param array $data Входные данные (одномерный или многомерный массив).
     * @return array Многомерный массив с данными.
     */
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
     * Метод для разбора значения и преобразования его в комплексное число (если необходимо).
     *
     * @param mixed $value Значение для разбора.
     * @return mixed Вещественное или комплексное число.
     */
    private function parseValue($value)
    {
        if (strpos($value, ':') !== false) {
            $parts = explode(':', $value);
            $real = (float) ($parts[0] ?: 0);
            $imaginary = (float) ($parts[1] ?: 0);
            return new ComplexNumber($real, $imaginary);
        }
        return (float) $value;
    }

/**
     * Метод для получения размерности N-мерного массива.
     *
     * @param array $array Многомерный массив.
     * @return array Массив с размерностями.
     */
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

    /**
     * Метод для получения значения элемента по индексам.
     *
     * @param array $indices Массив индексов для доступа к элементу.
     * @param bool $asComplexNumber Флаг, указывающий, что значение должно быть возвращено как комплексное число.
     * @return mixed Значение элемента (вещественное или комплексное число).
     */
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

    /**
     * Метод для установки значения элемента по индексам.
     *
     * @param array $indices Массив индексов для доступа к элементу.
     * @param mixed $value Значение для установки (вещественное или комплексное число).
     * @param bool $asComplexNumber Флаг, указывающий, что значение является комплексным числом.
     */
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

    /**
     * Вспомогательный метод для преобразования значения в комплексное число (если необходимо).
     *
     * @param mixed $value Значение для преобразования.
     * @return mixed Комплексное число или исходное значение.
     */
    private function ensureComplexNumber($value)
    {
        return is_a($value, 'ComplexNumber') ? $value : new ComplexNumber($value, 0);
    }
    /**
 * Отображает содержимое N-мерного массива в виде текста.
 */
public function display()
{
    $this->printArray($this->data);
}

/**
 * Вспомогательный рекурсивный метод для вывода массива в виде текста.
 *
 * @param array $array Многомерный массив для вывода.
 * @param int $indent Уровень отступа для вложенных массивов.
 */
private function printArray($array, $indent = 0)
{
    if (!is_array($array)) {
        echo str_repeat('  ', $indent) . $array . "\n";
    } else {
        foreach ($array as $element) {
            $this->printArray($element, $indent + 1);
        }
    }
}

    /**
     * Возвращает данные N-мерного массива.
     *
     * @return array Данные N-мерного массива.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Устанавливает данные N-мерного массива.
     *
     * @param array $data Данные N-мерного массива.
     */
    public function setData(array $data)
    {
        $this->data = $this->parseData($data);
    }
    /**
 * Возвращает размерности N-мерного массива.
 *
 * @return array Массив с размерностями.
 */



}
// Класс для двумерных массивов (матриц)
class Matrix extends NArray
{
    public function __construct($rows, $cols, $type = 'zero', $isComplex = false, $isPrime = false)
    {
        parent::__construct([$rows, $cols], $type, $isComplex);
        if ($isPrime) {
            $this->fillWithPrimes();
        }
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
            $result->setElement([$i, $j], $this->getElement([$i, $j])->add($other->getElement([$i, $j])));
        }
    }
    return $result;
}

public function multiply(Matrix $other)
{
    if ($this->dimensions[1] != $other->dimensions[0]) {
        throw new Exception("Matrices have invalid dimensions for multiplication");
    }

    $result = new Matrix($this->dimensions[0], $other->dimensions[1]);
    for ($i = 0; $i < $result->dimensions[0]; $i++) {
        for ($j = 0; $j < $result->dimensions[1]; $j++) {
            $sum = new ComplexNumber(0, 0);
            for ($k = 0; $k < $this->dimensions[1]; $k++) {
                $sum = $sum->add($this->getElement([$i, $k])->multiply($other->getElement([$k, $j])));
            }
            $result->setElement([$i, $j], $sum);
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
    $det = 1;

    for ($i = 0; $i < $n; $i++) {
        $maxRow = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if (abs($matrix[$j][$i]) > abs($matrix[$maxRow][$i])) {
                $maxRow = $j;
            }
        }

        if ($matrix[$maxRow][$i] == 0) {
            return 0; // Матрица вырожденная
        }

        if ($maxRow != $i) {
            $det = -$det;
            $temp = $matrix[$i];
            $matrix[$i] = $matrix[$maxRow];
            $matrix[$maxRow] = $temp;
        }

        $det *= $matrix[$i][$i];

        for ($j = $i + 1; $j < $n; $j++) {
            $factor = $matrix[$j][$i] / $matrix[$i][$i];
            for ($k = $i + 1; $k < $n; $k++) {
                $matrix[$j][$k] -= $factor * $matrix[$i][$k];
            }
        }
    }

    return $det;
}

private function luDeterminant()
{
    $n = $this->dimensions[0];
    $matrix = $this->data;
    $det = 1;

    for ($i = 0; $i < $n; $i++) {
        $maxRow = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if (abs($matrix[$j][$i]) > abs($matrix[$maxRow][$i])) {
                $maxRow = $j;
            }
        }

        if ($matrix[$maxRow][$i] == 0) {
            return 0; // Матрица вырожденная
        }

        if ($maxRow != $i) {
            $det = -$det;
            $temp = $matrix[$i];
            $matrix[$i] = $matrix[$maxRow];
            $matrix[$maxRow] = $temp;
        }

        $det *= $matrix[$i][$i];

        for ($j = $i + 1; $j < $n; $j++) {
            $factor = $matrix[$j][$i] / $matrix[$i][$i];
            for ($k = $i + 1; $k < $n; $k++) {
                $matrix[$j][$k] -= $factor * $matrix[$i][$k];
            }
        }
    }

    for ($i = 0; $i < $n; $i++) {
        $det *= $matrix[$i][$i];
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
        $n = $this->dimensions[0];
        $augmented = array_merge($this->data, array_fill(0, $n, array_fill(0, $n, 0)));
        for ($i = 0; $i < $n; $i++) {
            $augmented[$i] = array_merge($augmented[$i], [new ComplexNumber(1, 0)]);
        }

        for ($i = 0; $i < $n; $i++) {
            $maxRow = $i;
            for ($j = $i + 1; $j < $n; $j++) {
                if ($augmented[$j][$i]->abs() > $augmented[$maxRow][$i]->abs()) {
                    $maxRow = $j;
                }
            }

            if ($augmented[$maxRow][$i]->abs() == 0) {
                return null; // Матрица вырожденная
            }

            if ($maxRow != $i) {
                $temp = $augmented[$i];
                $augmented[$i] = $augmented[$maxRow];
                $augmented[$maxRow] = $temp;
            }

            $augmented[$i] = array_map(function ($value) use ($augmented, $i) {
                return $value->divide($augmented[$i][$i]);
            }, $augmented[$i]);

            for ($j = 0; $j < $n; $j++) {
                if ($j != $i) {
                    $factor = $augmented[$j][$i];
                    $augmented[$j] = array_map(function ($value, $other) use ($factor, $i) {
                        return $value->subtract($factor->multiply($other));
                    }, $augmented[$j], $augmented[$i]);
                }
            }
        }

        $inverse = array_map(function ($row) use ($n) {
            return array_slice($row, $n);
        }, $augmented);

        return new Matrix($n, $n, $inverse);
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
     * Выполняет LU-факторизацию матрицы с использованием алгоритма Дуліттла.
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

    for ($j = 0; $j < min($n, $m); $j++) {
        $x = $R->getColumnVector($j, $j);
        $sigma = $x->getElement([0])->multiply(new ComplexNumber(-$x->getElement([0])->sign(), 0));
        $u = $x->add($sigma);

        if ($u->norm() !== 0) {
            $v = $u->divide($u->norm());
            $P = $this->createIdentityMatrix($n);
            for ($i = 0; $i < $n; $i++) {
                $P->setElement([$i, $i], new ComplexNumber(2, 0));
            }
            $P = $P->subtract($this->createOuterProduct($v, $v)->multiply(new ComplexNumber(2, 0)));
            $R = $P->multiply($R);
            $Q = $Q->multiply($P);
        }
    }

    return [$Q, $R];
} */
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

public function subtract(Matrix $other)
{
    if ($this->dimensions != $other->dimensions) {
        throw new Exception("Matrices must have the same dimensions");
    }

    $result = new Matrix($this->dimensions[0], $this->dimensions[1]);
    for ($i = 0; $i < $this->dimensions[0]; $i++) {
        for ($j = 0; $j < $this->dimensions[1]; $j++) {
            $result->setElement([$i, $j], $this->getElement([$i, $j])->subtract($other->getElement([$i, $j])));
        }
    }
    return $result;
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
            $norm = sqrt(pow($r->abs(), 2) + pow($s->abs(), 2));

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
    // Реализации алгоритмов QR-факторизации

    /**
     * Выполняет QR-факторизацию матрицы с использованием алгоритма Грама-Шмидта.
     *
     * @return array Массив, содержащий матрицы Q и R.
     
    private function gramSchmidtQR()
    {
        $n = $this->dimensions[0];
        $m = $this->dimensions[1];
        $Q = $this->createIdentityMatrix($n);
        $R = clone $this;

        for ($j = 0; $j < $m; $j++) {
            $v = $R->getColumnVector($j);
            for ($i = 0; $i < $j; $i++) {
                $u = $Q->getColumnVector($i);
                $projection = $u->multiply($u->dotProduct($v)->divide($u->dotProduct($u)));
                $v = $v->subtract($projection);
            }
            $R->setColumnVector($j, $v);
            $Q->setColumnVector($j, $v->divide($v->norm()));
        }

        return [$Q, $R];
    }
    */
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
// Класс для векторов
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
            //case 'minkowski':
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

    private function minkowskiAngle(Vector $other, $p)
    {
        if ($this->dimensions != $other->dimensions) {
            throw new Exception("Vectors must have the same dimensions");
        }

        $diff = array_map(function ($a, $b) use ($p)  {
            return $a->subtract($b)->abs()->pow($p);
        }, $this->data[0], $other->data[0]);

        $minkowskiNorm = pow(array_sum($diff), 1 / $p);

        if ($minkowskiNorm == 0) {
            return 0; // Угол между двумя одинаковыми векторами
        }

        $cosine = 0;
        for ($i = 0; $i < $this->dimensions[1]; $i++) {
            $cosine += min($this->getElement([0, $i])->abs()->pow($p), $other->getElement([0, $i])->abs()->pow($p));
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


    public function kernelOfTransformation($transformation)
    {
        // Реализация нахождения ядра линейного преобразования
    }

    public function imageOfTransformation($transformation)
    {
        // Реализация нахождения образа линейного преобразования
    }

    public function add(Matrix $other)
{
    if ($this->dimensions != $other->dimensions) {
        throw new Exception("Vectors must have the same dimensions");
    }
    return new Vector(array_map(function ($a, $b) {
        return $a->add($b);
    }, $this->data[0], $other->data[0]));
}
/**
public function multiply($other)
{
    if (is_a($other, 'Vector')) {
        return $this->dotProduct($other);
    } elseif (is_a($other, 'Matrix')) {
        return parent::multiply($other);
    } else {
        return new Vector(array_map(function ($a) use ($other) {
            return $a->multiply(is_a($other, 'ComplexNumber') ? $other : new ComplexNumber($other, 0));
        }, $this->data[0]));
    }
}*/


    // Другие операции из Matrix, применимые к векторам
}

