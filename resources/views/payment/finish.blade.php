<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment {{ ucfirst($status) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            @if($status === 'success')
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-green-600 mb-2">{{ $message }}</h2>
            @elseif($status === 'pending')
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-yellow-600 mb-2">{{ $message }}</h2>
            @else
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-red-600 mb-2">{{ $message }}</h2>
            @endif
            
            @if($order_id)
                <p class="text-gray-600 mb-6">Order ID: <span class="font-medium text-gray-900">{{ $order_id }}</span></p>
            @endif
            
            <button 
                onclick="closeWebview()" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Back to App
            </button>
        </div>
    </div>

    <script>
        // Close webview or redirect to app
        function closeWebview() {
            // For mobile app webview
            if (window.ReactNativeWebView) {
                window.ReactNativeWebView.postMessage(JSON.stringify({
                    type: 'payment_result',
                    status: '{{ $status }}',
                    order_id: '{{ $order_id }}'
                }));
            } else {
                // Fallback for web browser
                window.close();
            }
        }
        
        // Auto close after 3 seconds for success
    </script>
    @if($status === 'success')
    <script>
        setTimeout(closeWebview, 3000);
    </script>
    @endif
</body>
</html>