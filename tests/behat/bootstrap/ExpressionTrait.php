<?php

namespace DigidepsBehat;

trait ExpressionTrait
{
    /**
     * @When I fill in :field with the value of :expression
     * See "expressionToValue()" for examples of expressions:
     */
    public function fillFieldWithExpression($field, $expression)
    {
        $this->fillField($field, self::expressionToValue($expression));
    }

    /**
     * @Then the field :field has value of :expression
     */
    public function theFieldHasValueOf($field, $expression)
    {
        $this->assertFieldContains($field, self::expressionToValue($expression));
    }

    /**
     * @Then I should see the value of :expression in the :region region
     */
    public function iShouldSeeTheValueOfInTheRegion($expression, $region)
    {
        $this->iShouldSeeInTheRegion(self::expressionToValue($expression), $region);
    }

    /**
     * Example of expressions
     * 3 days ago, DD
     * 10 days ahead, month
     * 1 day ahead, year.
     *
     * @param string $expression
     *
     * @return string
     *
     * @throws \RuntimeException if the expression is not recognised
     */
    private static function expressionToValue($expression)
    {
        if (preg_match('#^(?P<days>\d+) days? (?P<direction>ahead|ago), (?P<format>DD|MM|YYYY|DD/MM/YYYY)$#', $expression, $matches)) {
            $today = new \DateTime();
            $plusOrMinus = $matches['direction'] === 'ahead' ? '+' : '-';
            $today->modify($plusOrMinus.$matches['days'].' days');
            $formatToDateFormat = ['DD' => 'd', 'MM' => 'm', 'YYYY' => 'Y', 'DD/MM/YYYY' => 'd/m/Y'];

            return $today->format($formatToDateFormat[$matches['format']]);
        }

        throw new \RuntimeException("Expression '$expression' not recognised");
    }
}
