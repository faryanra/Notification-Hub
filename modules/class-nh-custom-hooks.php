<?php
// NH v1.2.0 — Custom Hook Builder (minimal placeholder for v1.2.0)

if (!defined('ABSPATH')) exit;

class NH_Custom_Hooks {
    protected $r;
    public function __construct($registry){ $this->r=$registry; }
    public function hooks() {
        // reserved for v1.3.0 advanced UI; keep minimal here
    }
}
