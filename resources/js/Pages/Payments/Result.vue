<template>
  <div>
    <Head title="Realizar Pagamento" />
    <h1 class="mb-8 text-3xl font-bold">
      <Link class="text-indigo-400 hover:text-indigo-600" href="/payments" >{{ this.title }}</Link>
      <span class="text-indigo-400 font-medium"></span> 
    </h1>

    <div class="flex items-center justify-center mt-[15%] bg-gray-100">
  <div class="bg-white p-8 rounded-lg shadow-lg text-center mt-[-50px]">

    <h1 class="text-2xl font-bold mb-4">{{ this.subtitle }}</h1>

    <div class="mb-4 text-left" v-if="hasData">
      <p v-if="this.data.data.invoiceNumber"><strong>Número do Pedido:</strong> {{ this.data.data.invoiceNumber }}</p>
      <p><strong>Valor:</strong> R$ {{ this.data.data.value }}</p>
      <p><strong>Meio de Pagamento:</strong> {{ this.data.data.billingType == 'CREDIT_CARD' ? 'CARTÃO DE CRÉDITO' : this.data.data.billingType }}</p>
      <p v-if="this.data.data.billingType == 'BOLETO'">
        <strong v-if="this.data.data.dueDateFormated">Data de Vencimento do Boleto:</strong> {{ this.data.data.dueDateFormated }}
      </p>
    </div>

    <p class="text-gray-700 mb-8">{{ this.data.message }}</p>

    <img v-if="hasData && this.data.data.billingType == 'PIX' && this.data.data.encodedImage"
        :src="'data:image/jpeg;base64, ' + this.data.data.encodedImage" 
        alt="Pagamento"
        class="mx-auto"
      />

    <br>
    <button v-if="hasData && this.data.data.billingType == 'PIX' && this.data.data.payload"
      @click="copyText" 
      class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition duration-300"
      ref="copyButton"
    >      
      Pix copia e cola
    </button><br>

    <a
      v-if="hasData && this.data.data.invoiceUrl"
      :href="this.data.data.invoiceUrl" 
      class="inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition duration-300 mt-10"
      target="_blank"
      rel="noopener noreferrer"
    >
      <strong>
        {{ this.message_link }}
      </strong> 

    </a>
  </div>
</div>

  </div>
</template>
  
  <script>
  import { Head, Link } from '@inertiajs/vue3'
  import Layout from '@/Shared/Layout.vue'
  import axios from 'axios'
  
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
      user: Object,
      errors: Object
    },
    data() {
      return {
        title: 'Pedido de Pagamento',
        subtitle: '',
        message_link: 'Visualizar Cobrança'
      }
    },
    computed: {
      hasData() {
        return this.data;

      }
    },
    methods: {
      async submitPayment() {
        try {
          const response = await axios.post('/payments', this.data.data)

          console.log('response payments', response)

          if (response.data.data && this.data.data.billingType != 'BOLETO') {
            const form = this.$inertia.form(response.data.data);

            form.post('/api/gateway-payments/finally', {
              headers: {
                "Accept": "application/json",
              },
            });

            console.log('finally data', this.data);
            console.log('finally message', this.message);
          }

        } catch (error) {
          this.error = error.response ? error.response.data : error.message;
          console.error('Request failed:', this.error);
        }
      },
      copyText() {
        const button = this.$refs.copyButton;
        const textToCopy = this.data.data.payload;
        
        // Cria um elemento de texto temporário
        const tempTextArea = document.createElement('textarea');
        tempTextArea.value = textToCopy;
        document.body.appendChild(tempTextArea);

        // Seleciona e copia o texto
        tempTextArea.select();
        document.execCommand('copy');

        // Remove o elemento de texto temporário
        document.body.removeChild(tempTextArea);

        // Feedback de cópia
        alert('Copiado!');
      }
    },
    mounted() {

      this.subtitle = this.data.success == "true" ? 'Obrigado, ' + this.user.first_name : 'Ops..';
      
      if (this.hasData) {
        var billingType = this.data.data.billingType;

        if (this.data.data.billingType == 'CREDIT_CARD' || this.data.data.billingType == 'UNDEFINED') {
          billingType = 'CARTÃO DE CRÉDITO';
          this.data.data.billingType = 'CREDIT_CARD';        }

        this.title = 'Pedido Pagamento - ' + billingType;       

       // this.submitPayment();

      }

    }
  }
  </script>
