<?php

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class AgClienteMathHelper
{
    private static function formatPrice($price)
    {
        if (class_exists(PriceFormatter::class)) {
            return (new PriceFormatter())->convertAndFormat($price);
        }

        return Tools::displayPrice($price);
    }

    public static function applyPriceVariation($value, $variation)
    {
        if (!$variation) {
            return $value;
        }

        //podemos ter duas variações de preço no mesmo campo: uma percentual e uma absoluto
        $variations = array('', '');
        $variation_index = 0;

        for ($i=0; $i<Tools::strlen($variation); $i++) {
            //os caracteres '+', '-' sempre começam uma nova variação de preço
            if ($i != 0 && in_array($variation[$i], array('+', '-'))) {
                $variation_index++;
            }

            $variations[$variation_index] .= $variation[$i];
        }

        $return = $value;

        for ($i=0; $i<2; $i++) {
            if (empty($variations[$i])) {
                continue;
            }

            $variation = $variations[$i];

            $signal = 1;
            $type   = 'absolute';
            if ($variation[0] === '-')
            {
                $signal = -1;
            }

            if ($variation[strlen($variation) - 1] === '%')
            {
                $type = 'percentage';
            }

            $variation = trim($variation, '+-%');
            if ($type === 'percentage') {
                $return += $value * ($signal * $variation) / 100;
            } else {
                $return += ($signal) * $variation;
            }
        }
            
        return $return;
    }


    /**
     *  Calcula o valor total do pagamento parcelado.
     *
     *  @param $options[qtt_instalments] - Quantidade de parcelas
     *  @param $options[interest_rate] - Taxa de juros por parcela
     *  @param $options[value] - Valor do pagamento à vista
     *
     */
    public static function applyInterestCompoundWithDiscount($options)
    {
        $v0 = $options['value'];
        $i = $options['interest_rate']/100;
        $n = $options['qtt_installments'];

        $num_p = $v0 * pow(1+$i, $n);

        $den_p = 0;

        for ($ii=1; $ii <= $n-1; $ii++) {
            $den_p++;

            for ($jj=1; $jj <= $ii-1; $jj++) {
                $den_p = $den_p + pow(1+$i, $jj);
            }
        }
        $den_p = $den_p * $i + $n;

        $p = $num_p / $den_p;
        return $n * $p;
    }

    public static function applyInterest($value, $interest_rate)
    {
        return $value * (1 + $interest_rate / 100);
    }

    /**
     *   Obtém o custo total e o valor da parcela para um pagamento parcelado.
     *
     *  @param $options[qtt_instalments_max] - Máximo de parcelas
     *  @param $options[installment_value_min] - Valor mínimo da parcela
     *  @param $options[qtt_installments_without_interest] - Quantidade de parcelas sem juros
     *  @param $options[value] - Valor do pagamento à vista
     *  @param $options[interest_rate] - Taxa de juros
     *  @param $options[interest_mode] - Tipo de Juros
     */

    public static function calcInstallments($options)
    {
        $return = [];

        for ($i=0; $i<$options['qtt_instalments_max']; $i++) {
            $options['qtt_installments'] = $i+1;

            if ($options['qtt_installments_without_interest'] > $i) {
                $total_value = $options['value'];
            } else {
                if (@$options['interest_mode'] == 0) {
                    $total_value = self::applyInterestCompoundWithDiscount($options);
                } elseif ($options['interest_mode'] == 1) {
                    $total_value = self::applyInterest($options['value'], $options['interest_rate'][$i+1]);
                }
            }

            $installment_value = $total_value / ($i+1);
            if ($installment_value < $options['installment_value_min'] && $i) {
                break;
            }

            $return[] = array(
                'total' => $total_value,
                'formatted_total' => self::formatPrice($total_value),
                'installment_value' => $installment_value,
                'formatted_installment_value' => self::formatPrice($installment_value)
            );
        }

        return $return;
    }
}
