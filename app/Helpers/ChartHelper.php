<?php

namespace App\Helpers;

class ChartHelper
{
    /**
     * Generate HTML-based chart for PDF using CSS styling
     * 
     * @param $data Collection of sensor data
     * @param $attribute String (suhu, ph, tds)
     * @param $minY Min value for Y axis
     * @param $maxY Max value for Y axis
     * @param $color Color of bars
     * @return String HTML
     */
    public static function svgLineChart($data, $attribute, $minY, $maxY, $color = '#ef4444')
    {
        if (!$data || $data->count() == 0) {
            return '<div style="padding: 20px; text-align: center; color: #999; border: 1px solid #ddd; border-radius: 4px;">Tidak ada data untuk ditampilkan</div>';
        }

        // Get values
        $values = $data->map(fn($item) => (float)($item->$attribute ?? 0))->toArray();
        
        if (empty($values)) {
            return '<div style="padding: 20px; text-align: center; color: #999; border: 1px solid #ddd; border-radius: 4px;">Tidak ada data untuk ditampilkan</div>';
        }

        // Calculate range
        $range = $maxY - $minY;
        if ($range <= 0) {
            $range = 1;
        }

        // Build HTML-based bar chart
        $html = '<div style="width: 100%; border: 1px solid #ddd; padding: 10px; background: #f9fafb; border-radius: 4px;">';
        $html .= '<div style="display: flex; align-items: flex-end; gap: 3px; height: 160px; padding-bottom: 10px; border-bottom: 1px solid #999; border-left: 1px solid #999; position: relative;">';
        
        // Y-axis labels (positioned absolutely on the left)
        $html .= '<div style="position: absolute; left: -35px; top: 0; height: 160px; display: flex; flex-direction: column; justify-content: space-between; font-size: 10px; color: #666; text-align: right; width: 30px;">';
        for ($i = 0; $i <= 4; $i++) {
            $val = $maxY - ($i * $range / 4);
            $html .= '<div>' . number_format($val, 1) . '</div>';
        }
        $html .= '</div>';
        
        // Bars
        foreach ($values as $value) {
            $normalizedValue = ($value - $minY) / $range;
            $normalizedValue = max(0, min(1, $normalizedValue)); // Clamp between 0-1
            $heightPercent = $normalizedValue * 100;
            
            $html .= '<div style="flex: 1; background: ' . htmlspecialchars($color) . '; height: ' . $heightPercent . '%; min-width: 8px; border-radius: 2px 2px 0 0; cursor: pointer;" title="' . number_format($value, 2) . '"></div>';
        }
        
        $html .= '</div>';
        
        // X-axis labels
        $html .= '<div style="display: flex; gap: 3px; margin-top: 8px; margin-left: -25px; font-size: 9px; color: #666;">';
        
        $step = max(1, (int)(count($values) / 8));
        $dataArray = $data->toArray();
        for ($i = 0; $i < count($values); $i += $step) {
            if (isset($dataArray[$i]->created_at)) {
                $timeStr = $dataArray[$i]->created_at->format('H:i');
            } else {
                $timeStr = $i;
            }
            $html .= '<div style="flex: ' . $step . '; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 8px;">' . htmlspecialchars($timeStr) . '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}
