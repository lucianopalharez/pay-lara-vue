<template>
  <div>
    <Head title="Realizar Pagamento" />
    <h1 class="mb-8 text-3xl font-bold">
      <Link class="text-indigo-400 hover:text-indigo-600" href="/payments">Realizar Pagamento</Link>
      <span class="text-indigo-400 font-medium"></span> 
    </h1>
    <div class="max-w-3xl bg-white rounded-md shadow overflow-hidden">
      <form @submit.prevent="store">
        <div class="flex flex-wrap -mb-8 -mr-6 p-8">
          <currency-input v-model="form.value" :options="{ currency: 'BRL' }" :error="form.errors.value" class="pb-8 pr-6 w-full lg:w-3/12" label="Valor R$" />

          <select-input v-model="form.billingType" onselect="" :error="form.errors.billingType" class="pb-8 pr-6 w-full lg:w-9/12" label="Meio Pagamento" @change="changeBillType">
            <option :value="null">Selecione o Meio de Pagamento</option>
            <option v-for="billingType in billingTypes" :key="billingType" :value="billingType">{{ billingType }}</option>
          </select-input>

          <text-input v-model="form.description" :error="form.errors.description" class="pb-8 pr-6 w-full lg:w-full" label="Descrição do pagamento" />
          
          <text-input v-if="form.billingType === 'CREDIT_CARD'" v-model="form.creditCardNumber" :error="form.errors.creditCardNumber" class="pb-8 pr-6 w-full lg:w-1/2" label="Numero do Cartão" 
          v-mask="['####.####.####.####']"/>
          <select-input v-if="form.billingType === 'CREDIT_CARD'" v-model="form.expiryMonth" :error="form.errors.expiryMonth" class="pb-8 pr-6 w-full lg:w-2/12" label="Mês">
            <option :value="null">Mês</option>
            <option v-for="month in 12" :key="month" :value="month">{{ month }}</option>
          </select-input>
          <select-input  v-if="form.billingType === 'CREDIT_CARD'"v-model="form.expiryYear" :error="form.errors.expiryYear" class="pb-8 pr-6 w-full lg:w-2/12" label="Ano">
            <option :value="null">Ano</option>
            <option v-for="ano in years" :key="ano" :value="ano">{{ ano }}</option>
          </select-input>          
          <text-input v-if="form.billingType === 'CREDIT_CARD'" v-model="form.cvv" :error="form.errors.cvv" class="pb-8 pr-6 w-full lg:w-2/12" label="CVV" v-mask="['###']" type="number"/>
        

          <text-input :disabled="form.billingType !== 'CREDIT_CARD'"  v-model="form.name" :error="form.errors.name" class="pb-8 pr-6 w-full lg:w-1/2" :label="form.billingType === 'CREDIT_CARD' ? 'Nome do titular impresso' : 'Nome Completo'" />
          <text-input v-model="form.cpfCnpj" :error="form.errors.cpfCnpj" class="pb-8 pr-6 w-full lg:w-1/2" label="CPF ou CNPJ" v-mask="['###.###.###-##', '##.###.###/####-##']" />
          
          <text-input v-model="form.phone" :error="form.errors.phone" class="pb-8 pr-6 w-full lg:w-1/2" label="Telefone" v-mask="['(##) ####-####', '(##) #####-####']" type="tel" />
          <text-input v-model="form.email" :error="form.errors.email" class="pb-8 pr-6 w-full lg:w-1/2" label="Email" />  
          <text-input v-model="form.postalCode" :error="form.errors.postalCode" class="pb-8 pr-6 w-full lg:w-4/12" label="CEP" v-mask="['#####-###']" />        
          <text-input v-model="form.address" :error="form.errors.address" class="pb-8 pr-6 w-full lg:w-8/12" label="Endereço" />
          
        </div>
        <div class="flex items-center justify-end px-8 py-4 bg-gray-50 border-t border-gray-100">
          <loading-button :loading="form.processing" class="btn-indigo" type="submit">Concluir</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import TextInput from '@/Shared/TextInput.vue'
import SelectInput from '@/Shared/SelectInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import CurrencyInput from '@/Shared/CurrencyInput.vue'
import {mask} from 'vue-the-mask'

export default {
  directives: {mask},
  components: {
    Head,
    Link,
    LoadingButton,
    SelectInput,
    TextInput,
    CurrencyInput
  },
  layout: Layout,
  props: {
    billingTypes: Array,
    user: Object
  },
  remember: 'form',
  data() {
    return {
      years: [],
      form: this.$inertia.form({
        description:'',
        name: this.customer,
        creditCardNumber: '',
        expiryMonth: '',
        expiryYear: '',
        cvv:'',
        billingType: null,
        email: '',
        phone: '',
        address: '',
        addressNumber: '',
        mobilePhone: '',
        postalCode: '',
        cpfCnpj: '202.416.790-01',
        value:'',
        userId:''
      }),
    }
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
    startYear() {
      return Math.max(1, this.currentYear - 10);
    },
    endYear() {
      return this.currentYear + 10;
    },
    customer() {
      return this.user.first_name + ' ' + this.user.last_name;
    }
  },
  created() {
    for (let year = this.startYear; year <= this.endYear; year++) {
      this.years.push(year);
    }
    this.form.name = this.customer;
    this.form.userId = this.user.id;
  },
  methods: {
    store() {
      this.form.post('/api/gateway-payments/generate', {
        headers: {
          "Accept": "application/json",
        },
        onSuccess: (page) => {
          console.log('this.form',page);
          console.log('Request was successful:', page.props);
        },
        onError: (errors) => {
          console.log('this.form',this.form);
          console.error('Request failed:', errors);
        }
      });
    },
    formatCpfCnpj(event) {
      console.log('Tecla pressionada:', event.key);
      // Aqui você pode adicionar a lógica que desejar
    },
    changeBillType() {
      if (this.form.billingType !== 'CREDIT_CARD') {
        this.form.name = this.customer;    
      }
    }
  },

}
</script>
