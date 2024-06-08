<template>
  <div>
    <Head title="Pagamentos" />
    <h1 class="mb-8 text-3xl font-bold">Lista de Pagamentos</h1>
    <div class="flex items-center justify-between mb-6">
      <search-filter v-model="form.search" class="mr-4 w-full max-w-md" @reset="reset">
      </search-filter>
      <Link class="btn-indigo" href="/payments/create">
        <span>Realizar Pagamento</span>
      </Link>
    </div>

    <div class="bg-white rounded-md shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap">
        <tr class="text-left font-bold">
          <th class="pb-4 pt-6 px-6">Código</th>
          <th class="pb-4 pt-6 px-6">Valor</th>
          <th class="pb-4 pt-6 px-6">Meio Pagamento</th>
          <th class="pb-4 pt-6 px-6">Status</th>
          <th class="pb-4 pt-6 px-6">Data</th>
          <th class="pb-4 pt-6 px-6">Pagar</th>
        </tr>
        <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t">
            {{ payment.invoiceNumber }}
          </td>
          <td class="border-t">
            R$ {{ payment.value }}
          </td>
          <td class="border-t">
            {{ payment.billingType == 'CREDIT_CARD' ? 'CARTÃO DE CRÉDITO' : payment.billingType }}
          </td>
          <td class="border-t">
            {{ payment.status }}
          </td>
          <td class="border-t">
            {{ payment.created }}
          </td>
          <td class="w-px border-t">
            <a class="bg-blue-500 text-white flex items-center px-4" target="_blank" :href="payment.invoiceUrl" tabindex="-1">
              Pagar
            </a>
          </td>
        </tr>
        <tr v-if="payments.data.length === 0">
          <td class="px-6 py-4 border-t" colspan="4">Nenhum pagamento encontrado</td>
        </tr>
      </table>
    </div>
    <pagination class="mt-6" :links="payments.links" />
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout.vue'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import Pagination from '@/Shared/Pagination.vue'
import SearchFilter from '@/Shared/SearchFilter.vue'

export default {
  components: {
    Head,
    Icon,
    Link,
    Pagination,
    SearchFilter,
  },
  layout: Layout,
  props: {
    filters: Object,
    payments: Object,
  },
  data() {
    return {
      form: {
        search: this.filters.search,
      },
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/payments', pickBy(this.form), { preserveState: true })
      }, 150),
    },
  },
  methods: {
    reset() {
      this.form = mapValues(this.form, () => null)
    },
  },
}
</script>
