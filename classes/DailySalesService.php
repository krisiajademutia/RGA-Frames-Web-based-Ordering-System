<?php
class DailySalesService {
    private $repository;

    public function __construct(IDailySalesRepository $repository) {
        $this->repository = $repository;
    }

    public function getFormattedSalesReport() {
        $rawData = $this->repository->getDailySalesData();
        $formattedData = [];

        foreach ($rawData as $row) {
            $readyMade = (int)$row['ready_made_qty'];
            $custom = (int)$row['custom_qty'];
            
            $formattedData[] = [
                // Formats to "Feb 20, 2026" like Figma
                'date' => date('M d, Y', strtotime($row['sale_date'])),
                'ready_made' => $readyMade,
                'custom' => $custom,
                'total_sold' => $readyMade + $custom,
                // Formats to "3,450"
                'earnings' => number_format((float)$row['daily_earnings'], 0, '.', ',')
            ];
        }

        return $formattedData;
    }
}
?>