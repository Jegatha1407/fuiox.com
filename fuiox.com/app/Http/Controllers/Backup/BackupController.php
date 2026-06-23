<?php

namespace App\Http\Controllers\Backup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use ZipArchive;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use App\Models\Support;

use App\Models\BackupHistory;
use Symfony\Component\Process\Process;
use App\Services\BrevoService;

class BackupController extends Controller
{
    
    public function file_db_backup()
    {
        try {
            Log::info(" Backup process started");
            // $this->backupProject();
            $this->backupDatabase();
            Log::info(" Project and Database backup completed successfully.");
            return [
                'status'  => true,
                'message' => " Project and Database backup created successfully.",
            ];
        } catch (\Exception $e) {
            Log::error(" Backup Failed: " . $e->getMessage());
            return [
                'status'  => false,
                'message' => " Backup failed: " . $e->getMessage(),
            ];
        }
    }


    private function backupDatabase()
    {
        try {
            Log::info(" Database backup started");

            $db   = env('DB_DATABASE');
            $user = env('DB_USERNAME');
            $pass = env('DB_PASSWORD');
            $host = env('DB_HOST');

            $backupDir = storage_path('app/public/backups');

            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0777, true);
            }

            $oldFiles = glob($backupDir . '/*');
            foreach ($oldFiles as $oldFile) {
                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }

            $sqlFileName = 'db_backup_' . now()->format('Ymd_His') . '.sql';
            $sqlFilePath = $backupDir . '/' . $sqlFileName;

            $zipFileName = 'db_backup_latest.zip';
            $zipFilePath = $backupDir . '/' . $zipFileName;

            $process = new Process([
                'mysqldump',
                '--host=' . $host,
                '--user=' . $user,
                '--password=' . $pass,
                '--skip-extended-insert',
                '--complete-insert',
                '--skip-comments',
                $db,
            ]);

            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('MYSQLDUMP ERROR : ' . $process->getErrorOutput());

                BackupHistory::create([
                    'file_name'     => $zipFileName,
                    'file_size'     => '0 MB',
                    'backup_status' => 'failed',
                    'mail_status'   => 'failed',
                ]);

                throw new \Exception($process->getErrorOutput());
            }

            file_put_contents($sqlFilePath, $process->getOutput());

            $zip = new \ZipArchive();

            if ($zip->open($zipFilePath, \ZipArchive::CREATE) === true) {
                $zip->addFile($sqlFilePath, $sqlFileName);
                $zip->close();
            } else {
                throw new \Exception('Unable to create ZIP file');
            }
            unlink($sqlFilePath);

            Log::info(" Database backup ZIP completed");

            $fileSize = round(filesize($zipFilePath) / 1024 / 1024, 2) . ' MB';
            $mailStatus = 'sent';

            try {
               $brevo = new BrevoService();
$brevo->sendBackupMail(
    'novelxansalna@gmail.com',
    'Novelx',
    'Database Backup',
    'emails.database_backup',
    $zipFilePath
);

                Log::info("Backup mail sent successfully");
            } catch (\Exception $e) {
                $mailStatus = 'failed';
                Log::error(" Mail Send Failed : " . $e->getMessage());
            }

            BackupHistory::create([
                'file_name'     => $zipFileName,
                'file_size'     => $fileSize,
                'backup_status' => 'success',
                'mail_status'   => $mailStatus,
            ]);

            return [
                'status'        => true,
                'file_name'     => $zipFileName,
                'file_size'     => $fileSize,
                'backup_status' => 'success',
                'mail_status'   => $mailStatus,
            ];

        } catch (\Exception $e) {
            Log::error(' BACKUP ERROR : ' . $e->getMessage());
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
