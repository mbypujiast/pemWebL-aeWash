<span class="badge-status s-{{ $status }}">{{ \App\Models\Order::STATUSES[$status] ?? $status }}</span>
