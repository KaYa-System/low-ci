<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected UserTrackingService $trackingService;

    public function __construct(UserTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Obtient toutes les statistiques de la dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        // Parse les dates optionnelles
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $data = [
            'general' => $this->trackingService->getGeneralStats($startDate, $endDate),
            'countries' => $this->trackingService->getCountryStats($startDate, $endDate),
            'devices' => $this->trackingService->getDeviceStats($startDate, $endDate),
            'browsers' => $this->trackingService->getBrowserStats($startDate, $endDate),
        ];

        return response()->json([
            'data' => $data,
            'message' => 'Statistiques récupérées avec succès'
        ]);
    }

    /**
     * Statistiques par pays
     */
    public function countries(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $countries = $this->trackingService->getCountryStats($startDate, $endDate);

        return response()->json([
            'data' => $countries,
            'message' => 'Statistiques par pays récupérées avec succès'
        ]);
    }

    /**
     * Statistiques par appareils
     */
    public function devices(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $devices = $this->trackingService->getDeviceStats($startDate, $endDate);

        return response()->json([
            'data' => $devices,
            'message' => 'Statistiques par appareil récupérées avec succès'
        ]);
    }

    /**
     * Statistiques par navigateurs
     */
    public function browsers(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $browsers = $this->trackingService->getBrowserStats($startDate, $endDate);

        return response()->json([
            'data' => $browsers,
            'message' => 'Statistiques par navigateur récupérées avec succès'
        ]);
    }

    /**
     * Statistiques générales
     */
    public function general(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $stats = $this->trackingService->getGeneralStats($startDate, $endDate);

        return response()->json([
            'data' => $stats,
            'message' => 'Statistiques générales récupérées avec succès'
        ]);
    }

    /**
     * Export des données (CSV)
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');
        $type = $request->input('type', 'general'); // general, countries, devices, browsers
        
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        // Récupère les données selon le type
        switch ($type) {
            case 'countries':
                $data = $this->trackingService->getCountryStats($startDate, $endDate);
                $filename = 'analytics_countries.csv';
                break;
            case 'devices':
                $data = $this->trackingService->getDeviceStats($startDate, $endDate);
                $filename = 'analytics_devices.csv';
                break;
            case 'browsers':
                $data = $this->trackingService->getBrowserStats($startDate, $endDate);
                $filename = 'analytics_browsers.csv';
                break;
            default:
                $data = $this->trackingService->getGeneralStats($startDate, $endDate);
                $filename = 'analytics_general.csv';
        }

        if ($format === 'csv') {
            return $this->exportToCsv($data, $filename);
        }

        return response()->json(['message' => 'Format non supporté'], 400);
    }

    /**
     * Export vers CSV
     */
    private function exportToCsv($data, string $filename)
    {
        $output = fopen('php://output', 'w');
        
        ob_start();
        
        // En-têtes CSV selon le type de données
        if (!empty($data)) {
            $firstRow = is_object($data[0]) ? (array) $data[0] : $data[0];
            fputcsv($output, array_keys($firstRow));
            
            foreach ($data as $row) {
                $row = is_object($row) ? (array) $row : $row;
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        $csvContent = ob_get_clean();
        
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
