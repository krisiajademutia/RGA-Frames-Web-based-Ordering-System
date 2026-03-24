<?php
namespace Classes\Dashboard;

use Classes\Dashboard\Repository\IDailySalesRepository;

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
            $printing = (int)$row['printing_qty']; // 🔥 Fetch the printing count!
            
            $formattedData[] = [
                'date' => date('M d, Y', strtotime($row['sale_date'])),
                'ready_made' => $readyMade,
                'custom' => $custom,
                'printing' => $printing, // 🔥 Pass it to the frontend
                'total_sold' => $readyMade + $custom + $printing, // 🔥 Add it to the total!
                'earnings' => number_format((float)$row['daily_earnings'], 2, '.', ',')
            ];
        }

        return $formattedData;
    }

    public function getTodaysCombinedBreakdown() {
        return $this->repository->getTodaysCombinedBreakdown();
    }
}
?>