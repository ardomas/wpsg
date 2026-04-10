<?php
if (!defined('ABSPATH')) exit;

class WPSG_ContentsService {

    protected $repo;

    protected $allowed_types = [
        'announcement',
        'program',
        'activity',
        'service',
        'faq',
        'resource',
    ];

    public function __construct() {
        $this->repo = new WPSG_PostsRepository();
    }

    protected function assert_post_type(string $type): void {
        if (!in_array($type, $this->allowed_types, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid content type: %s', $type)
            );
        }
    }

    public function create(string $type, array $data): int {
        $this->assert_post_type($type);

        $payload = array_merge([
            'post_type' => $type,
            'status'    => 'draft',
            'created_at'=> current_time('mysql'),
        ], $data);

        return $this->repo->create($payload);
    }

    public function update(int $id, array $data): bool {
        return $this->repo->update($id, $data);
    }

    public function get_list( string $type, array $args = [] ) {
        $this->assert_post_type($type);

        $args['post_type'] = $type;

        return $this->repo->get_list($args);
    }

    public function get(int $id) {
        return $this->repo->find($id);
    }

    public function set_meta(
        int $content_id,
        string $key,
        $value
    ): bool {
        return $this->repo->set_meta($content_id, $key, $value);
    }

    public function get_meta(
        int $content_id,
        string $key = null
    ) {
        return $this->repo->get_meta($content_id, $key);
    }

    public function delete(int $id): bool {
        return $this->repo->delete($id);
    }

}