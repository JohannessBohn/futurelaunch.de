<?php
class NewsletterStorage {
    private $storageFile;
    
    public function __construct() {
        $this->storageFile = __DIR__ . '/../data/newsletter_subscribers.json';
        $this->ensureStorageExists();
    }
    
    private function ensureStorageExists() {
        $dir = dirname($this->storageFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!file_exists($this->storageFile)) {
            file_put_contents($this->storageFile, json_encode([], JSON_PRETTY_PRINT));
        }
    }
    
    public function addSubscriber($email) {
        $subscribers = $this->getSubscribers();
        if (!in_array($email, $subscribers)) {
            $subscribers[] = [
                'email' => $email,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ];
            $this->saveSubscribers($subscribers);
            return true;
        }
        return false;
    }
    
    public function getSubscribers() {
        if (!file_exists($this->storageFile)) {
            return [];
        }
        $data = file_get_contents($this->storageFile);
        return json_decode($data, true) ?: [];
    }
    
    public function deleteSubscriber($email) {
        $subscribers = $this->getSubscribers();
        $filtered = array_filter($subscribers, function($sub) use ($email) {
            return $sub['email'] !== $email;
        });
        
        if (count($filtered) < count($subscribers)) {
            $this->saveSubscribers(array_values($filtered));
            return true;
        }
        return false;
    }
    
    private function saveSubscribers($subscribers) {
        file_put_contents($this->storageFile, json_encode(array_values($subscribers), JSON_PRETTY_PRINT));
    }
    
    public function exportToCsv() {
        $subscribers = $this->getSubscribers();
        $csv = "Email,Registered At,Status\r\n";
        
        foreach ($subscribers as $sub) {
            $csv .= sprintf(
                '"%s","%s","%s"%s',
                str_replace('"', '""', $sub['email']),
                $sub['created_at'],
                $sub['status'],
                "\r\n"
            );
        }
        
        return $csv;
    }
}
?>
