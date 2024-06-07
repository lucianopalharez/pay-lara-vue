<template>
    <div>
      <Head title="Realizar Pagamento" />
      <h1 class="mb-8 text-3xl font-bold">
        <Link class="text-indigo-400 hover:text-indigo-600" href="/payments">Pedido Aguardando Pagamento</Link>
        <span class="text-indigo-400 font-medium"></span> 
      </h1>

      <div class="flex items-center justify-center mt-[15%] bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg text-center mt-[-50px]">
      <h1 class="text-2xl font-bold mb-4">Obrigado, {{ user.first_name }}!</h1>
      <div class="mb-4 text-left">
        <p><strong>Número do Pedido:</strong> {{ this.data.data.invoiceNumber }}</p>
        <p><strong>Valor:</strong> R$ {{ this.data.data.value }}</p>
        <p><strong>Meio de Pagamento:</strong> {{ this.data.data.billingType }}</p>
        <p><strong>Data de Vencimento do Boleto:</strong> {{ this.data.data.dueDate }}</p>
        
        
      </div>
      <p class="text-gray-700 mb-8">{{ this.message }}</p>
      <a 
        :href="this.data.data.invoiceUrl" 
        class="inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition duration-300"
        target="_blank"
        rel="noopener noreferrer"
      >
        Pagar Boleto
      </a>
    </div>
  </div>

    </div>
  </template>
  
  <script>
  import { Head, Link } from '@inertiajs/vue3'
  import Layout from '@/Shared/Layout.vue'
  
  export default {
    components: {
      Head,
      Link,
    },
    layout: Layout,
    props: {
      success: Boolean,
      status: Number, 
      data: Object, 
      message: String,
      user: Object
    },
    created() {
      console.log(this.data)
    }
  }
  </script>

