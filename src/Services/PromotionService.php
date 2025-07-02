<?php

declare(strict_types=1);

namespace Ovlk\Chatbot\Services;

use Core\Database;

class PromotionService
{
    private Database $db;

    private MetaApiService $metaApi;

    public function __construct(Database $db, MetaApiService $metaApi)
    {
        $this->db = $db;
        $this->metaApi = $metaApi;
    }

    public function createRegistration(array $data): void
    {
        $this->db->beginTransaction();
        try {
            // Insere os dados básicos no banco
            $this->db->queryBuilder(
                "INSERT INTO promotions (name, cpf, email, whatsapp_number, meta_message_id, status) VALUES (:name, :cpf, :email, :whatsapp_number, :meta_message_id, 'pending')",
                [
                    ':name' => $data['name'],
                    ':cpf' => $data['cpf'],
                    ':email' => $data['email'],
                    ':whatsapp_number' => $data['whatsapp_number'],
                    ':meta_message_id' => $data['meta_message_id'],
                ]
            );

            // Faz o download do comprovante
            $receiptId = $data['receipt_image_id'];
            $filename = 'receipt_'.$receiptId.'.jpg';
            $uploadDir = base_path('public/uploads/');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $filePath = $uploadDir.$filename;

            $downloaded = $this->metaApi->downloadMedia($receiptId, $filePath);

            if ($downloaded) {
                // Atualiza o registro com o caminho do arquivo e o status
                $this->db->queryBuilder(
                    "UPDATE promotions SET receipt_path = :path, status = 'completed' WHERE meta_message_id = :meta_message_id",
                    [':path' => 'uploads/'.$filename, ':meta_message_id' => $data['meta_message_id']]
                );
            } else {
                $this->db->queryBuilder(
                    "UPDATE promotions SET status = 'error' WHERE meta_message_id = :meta_message_id",
                    [':meta_message_id' => $data['meta_message_id']]
                );
            }

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollBack();
            // Log do erro (importante!)
            error_log('Falha no cadastro: '.$e->getMessage());
            $this->metaApi->sendMessage($data['whatsapp_number'], 'Ops! Ocorreu um erro ao processar seu cadastro. Tente novamente mais tarde.');
        }
    }
}
