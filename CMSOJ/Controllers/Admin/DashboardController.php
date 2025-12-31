<?php

namespace CMSOJ\Controllers\Admin;

use CMSOJ\Template;
use CMSOJ\Core\Database;

class DashboardController
{
  public function index()
  {
    $db = Database::connect();

    $totalAccounts = $db->query("SELECT COUNT(*) FROM accounts")->fetchColumn();
    $totalMessages = $db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    $unreadMessages = $db->query("SELECT COUNT(*) FROM messages WHERE status = 'Unread'")->fetchColumn();
    $totalEvents = $db->query("SELECT COUNT(*) FROM events")->fetchColumn();


    return Template::view('CMSOJ/Views/admin/dashboard.html', [
      'title' => 'Admin Dashboard',
      'display_name' => $_SESSION['account_name'] ?? 'no_name',
      'totalAccounts' => $totalAccounts,
      'totalMessages' => $totalMessages,
      'unreadMessages' => $unreadMessages,
      'totalEvents' => $totalEvents,
    ]);
  }
}
