<?php

declare(strict_types=1);

namespace netFantom\RobokassaApi\Options;

use DateTimeInterface;
use netFantom\RobokassaApi\Params\Option\{Culture, OutSumCurrency, Receipt};

class InvoiceOptions
{
    public readonly string $outSum;
    public readonly string|null $expirationDate;

    /**
     * @param float|string $outSum Требуемая к получению сумма
     * (буквально — стоимость заказа, сделанного клиентом). Формат представления — число,
     * разделитель — точка, например: 123.45.
     * Сумма должна быть указана в рублях.
     * Но, если стоимость товаров у Вас на сайте указана, например, в долларах,
     * то при выставлении счёта к оплате Вам необходимо указывать уже пересчитанную сумму из долларов в рубли.
     * {@see self::$outSumCurrency}
     * @param int|null $invId Номер счета в магазине.
     * <s>Необязательный параметр, но</s> мы настоятельно рекомендуем его использовать.
     * Значение этого параметра должно быть уникальным для каждой оплаты.
     * Может принимать значения от 1 до 2147483647 (2 - 1).
     *
     * Если значение параметра пустое, или равно 0, или параметр вовсе не указан,
     * то при создании операции оплаты ему автоматически будет присвоено уникальное значение.
     * @param string $description Описание покупки можно использовать только символы английского или русского алфавита,
     * цифры и знаки препинания. Максимальная длина — 100 символов. Эта информация отображается в интерфейсе
     * ROBOKASSA и в Электронной квитанции, которую мы выдаём клиенту после успешного платежа.
     * Корректность отображения зависит от необязательного параметра {@see self::$encoding}
     * @param Receipt|string|null $receipt Данные для фискализации
     * @param DateTimeInterface|string|null $expirationDate <pre>
     * Срок действия счета. Этот параметр необходим, чтобы запретить пользователю
     * оплату позже указанной магазином даты при выставлении счета.
     *
     * <b>Дата и время передаются в формате по стандарту ISO 8601:</b>
     * <i>YYYY-MM-DDThh:mm:ss.fffffff;ZZZZZ</i>
     * * (РЕКОМЕНДУЮ передавать <b>new DateTimeImmutable(...)</b> для автоматического форматирования)
     *
     * <b>Например:</b>
     * <i>2010-02-11T16:07:11.6973153+03:00</i>
     * * (ИЛИ <b>new DateTimeImmutable('2010-02-11 16:07:11', new DateTimeZone('+3'))</b> для автоматического форматирования)
     * * (ИЛИ <b>(new DateTimeImmutable())->add(new DateInterval('PT48H'))</b> чтобы задать время жизни счета 48 часов)
     *
     * <i>Формат содержит параметры:
     * YYYY — Год, 4 цифры
     * MM - Месяц, 2 цифры
     * DD - День месяца, 2 цифры (от 01 до 31)
     * T - Латинский символ «T» в верхнем регистре
     * hh - Часы, 2 цифры (24-часовой формат, от 00 до 23)
     * mm - Минуты, 2 цифры (от 00 до 59)
     * ss - Секунды, 2 цифры (от 00 до 59)
     * fffffff - Дробные части секунд от 1 до 7 цифр
     * ZZZZZ - Описатель временной зоны. Должен быть в верхнем регистре.
     * Означает, что момент времени представлен в UTC зоне (эквивалентно +00:00 и -00:00).
     * Смещение -hh:mm или +hh:mm относительно UTC показывает, что указано локальное время,
     * которое на данное число часов и минут опережает или отстает от UTC
     * </i></pre>
     * @param string|null $email Email покупателя автоматически подставляется в платёжную форму ROBOKASSA.
     * Пользователь может изменить его в процессе оплаты.
     * @param OutSumCurrency|null $outSumCurrency Способ указать валюту
     * в которой магазин выставляет стоимость заказа.
     * Этот параметр нужен для того, чтобы избавить магазин от самостоятельного пересчета по курсу.
     * Является дополнительным к обязательному параметру {@see self::$outSum}
     * Если этот параметр присутствует, то {@see self::$outSum} показывает полную сумму заказа, конвертированную
     * из той валюты, которая указана в параметре OutSumCurrency, в рубли по курсу ЦБ на момент оплаты.
     * Принимает значения: USD, EUR и KZT.
     * @param string|null $userIP Ip конечного пользователя
     * Передача этого параметра желательна для усиления безопасности,
     * предотвращению фрода и противодействию мошенникам.
     * Этот параметр пользователь передает при оплате.
     * При расчете контрольной суммы {@see self::$userIP} ставится перед "Пароль#1"
     * (кроме случаев использования параметра {@see self::$Receipt}).
     * @param string|null $incCurrLabel Предлагаемый способ оплаты.
     * Тот вариант оплаты, который Вы рекомендуете использовать своим покупателям
     * (если не задано, то по умолчанию открывается оплата Банковской картой).
     * Если параметр указан, то покупатель при переходе на сайт ROBOKASSA попадёт на страницу оплаты
     * с выбранным способом оплаты.
     * @param array<string, string> $userParameters <pre>
     * Дополнительные пользовательские параметры.
     * Они также относятся к необязательным параметрам, но несут совершенно другую смысловую нагрузку.
     * Это такие параметры, которые Robokassa никак не обрабатывает, но всегда возвращает магазину в ответных вызовах.
     *
     * Их следует указывать при старте операции оплаты если:
     * - вы собираетесь создавать магазин, в котором предусмотрено большое количество товаров, разделов и типов товара;
     * - ваш сайт будет предоставлять разнообразные услуги, не похожие друг на друга;
     * - на одном сайте работают несколько ресурсов;
     * - и самое распространённое — вам нужно использовать дополнительную идентификацию ваших клиентов, например,
     * знать его ID или Логин.
     * При завершении операции оплаты, мы будем возвращать вам эти дополнительные параметры.
     *
     * Они всегда должны начинаться с "Shp_", "SHP_" или "shp_".
     *
     * Например:
     * Shp_1=1:Shp_1=2:Shp_oplata=1:Shp_oplata=2:Shp_login=Vasya:Shp_name=Вася:Shp_url=https://robokassa.com/
     * </pre>
     * @param string $encoding Кодировка, в которой отображается страница ROBOKASSA.
     * По умолчанию: utf-8. Этот же параметр влияет на корректность отображения описания покупки
     * ({@see self::$description})
     * в интерфейсе ROBOKASSA, и на правильность передачи Дополнительных пользовательских параметров,
     * если в их значениях присутствует язык отличный от английского.
     * @param Culture|null $culture Язык общения с клиентом
     * (в соответствии с ISO 3166-1).
     * Определяет на каком языке будет страница ROBOKASSA, на которую попадёт покупатель.
     * Может принимать значения: en, ru.
     * Если параметр не передан, то используются региональные настройки браузера покупателя.
     * Для значений отличных от ru или en используется английский язык.
     * @param string|null $signatureValue
     * @param int|null $previousInvoiceId - айди прошлого платежа, для проведения рекуррентных оплат
     * доступна только при подверждение такого типа оплат в робокассе
     */
    public function __construct(
        float|string $outSum,
        public readonly int|null $invId,
        public readonly string $description,
        public readonly int|null $previousInvoiceId = null,
        public readonly Receipt|string|null $receipt = null,
        DateTimeInterface|string|null $expirationDate = null,
        public readonly ?string $email = null,
        public readonly ?OutSumCurrency $outSumCurrency = null,
        public readonly ?string $userIP = null,
        public readonly ?string $incCurrLabel = null,
        public readonly array $userParameters = [],
        public readonly string $encoding = 'utf-8',
        public readonly ?Culture $culture = null,
        public readonly ?string $signatureValue = null,
    ) {
        if ($expirationDate instanceof DateTimeInterface) {
            $this->expirationDate = $expirationDate->format("Y-m-d\TH:i:s.0000000P");
        } else {
            $this->expirationDate = $expirationDate;
        }
        $this->outSum = number_format(num: (float)$outSum, decimals: 2, thousands_separator: '');
    }

    /**
     * @return array<string, string>
     */
    public function getFormattedUserParameters(): array
    {
        $formattedParameters = [];
        foreach ($this->userParameters as $key => $value) {
            $formattedParameters["shp_$key"] = $value;
        }
        ksort($formattedParameters);
        return $formattedParameters;
    }
}
