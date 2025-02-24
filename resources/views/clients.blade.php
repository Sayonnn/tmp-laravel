<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Clients') }}
        </h2>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    Here are the list of your clients:
                </div>
                @foreach ($clients as $client)
                    <div class="py-3 text-gray-900 border border-gray-200 p-2 ">
                        <h3 class="text-lg text-gray-500 text-xs"><b>Client ID:</b>{{ $client->id }}</h3>
                        <h3 class="text-lg text-gray-500 text-xs"><b>Client Name:</b>{{ $client->name }}</h3> 
                        <h3 class="text-lg text-gray-500 text-xs"><b>Client Secret:</b>{{ $client->secret }}</h3> 
                        <h3 class="text-lg text-gray-500 text-xs"><b>Client Callback:</b>{{ $client->redirect }}</h3> 
                        <form action="{{ url('/oauth/clients/' . $client->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="border p-4 bg-gray-400 rounded-lg">
                                Remove
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 p-6 bg-white border-b border-gray-200">
                <form action="/oauth/clients" method="POST" class="flex flex-col gap-4">
                    <div class="flex gap-4 align-items-center justify-start">
                        <label for="name" class="w-[200px]">Name</label>
                        <input class="border border-gray-200 rounded-lg" type = "text" name = "name"
                            placeholder = "Client Name">
                    </div>
                    <div class="flex gap-4 align-items-center justify-start">
                        <label for="redirect" class="w-[200px]">Redirect</label>
                        <input class="border border-gray-200 rounded-lg" type = "text" name = "redirect"
                            placeholder="https://my-url.com/callback">
                    </div>
                    <div>
                        @csrf
                        @method('POST')
                        <button type="submit" class="border p-4 bg-gray-400 rounded-lg">
                            Create Client
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
