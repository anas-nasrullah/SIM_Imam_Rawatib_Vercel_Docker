<?php
declare(strict_types=1);
// Session persisten di MySQL (bukan filesystem Vercel Function).
final class DatabaseSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface {
    public function __construct(private PDO $pdo) {}
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }
    public function read(string $id): string|false {
        try {
            $q = $this->pdo->prepare('SELECT payload FROM app_sessions WHERE id=? AND expires_at > UTC_TIMESTAMP() LIMIT 1');
            $q->execute([$id]);
            $result = $q->fetchColumn();
            return $result === false ? '' : (string)$result;
        } catch (Throwable $e) { error_log('Session read error'); return false; }
    }
    public function write(string $id, string $data): bool {
        try {
            $expires = gmdate('Y-m-d H:i:s', time()+max(600,(int)ini_get('session.gc_maxlifetime')));
            $q = $this->pdo->prepare('INSERT INTO app_sessions (id,payload,expires_at) VALUES (?,?,?) ON DUPLICATE KEY UPDATE payload=VALUES(payload), expires_at=VALUES(expires_at)');
            return $q->execute([$id,$data,$expires]);
        } catch (Throwable $e) { error_log('Session write error'); return false; }
    }
    public function destroy(string $id): bool {
        try { $q=$this->pdo->prepare('DELETE FROM app_sessions WHERE id=?'); return $q->execute([$id]); }
        catch (Throwable $e) { error_log('Session destroy error'); return false; }
    }
    public function gc(int $max_lifetime): int|false {
        try { return $this->pdo->exec('DELETE FROM app_sessions WHERE expires_at <= UTC_TIMESTAMP()'); }
        catch (Throwable $e) { error_log('Session gc error'); return false; }
    }
    public function validateId(string $id): bool {
        try { $q=$this->pdo->prepare('SELECT 1 FROM app_sessions WHERE id=? AND expires_at>UTC_TIMESTAMP() LIMIT 1'); $q->execute([$id]); return (bool)$q->fetchColumn(); }
        catch (Throwable $e) { return false; }
    }
    public function updateTimestamp(string $id, string $data): bool { return $this->write($id, $data); }
}
