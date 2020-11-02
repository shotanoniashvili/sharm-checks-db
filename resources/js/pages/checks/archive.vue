<template>
  <card>
    <div class="row mb-5">
      <div class="col-12 col-lg-6">
        <div class="card-title">
          ჩეკების არქივი
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="row">
          <div class="col-3">
            <b-form-datepicker
              v-model="searchParams.dateFrom"
              :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
              class="mb-2" placeholder="თარიღი (-დან)"
            />
          </div>
          <div class="col-3">
            <b-form-datepicker
              v-model="searchParams.dateTo"
              :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
              class="mb-2" placeholder="თარიღი (-მდე)"
            />
          </div>
          <div class="col-3">
            <v-select
              v-model="searchParams.operators"
              :reduce="operator => operator.id"
              label="name"
              multiple
              :options="operators"
              placeholder="მომხმარებელი"
            />
          </div>
          <div class="col-2">
            <b-button @click="loadChecks">
              <b-icon icon="search" />
            </b-button>
          </div>
        </div>
      </div>
      <div class="col-12">
        <b-button variant="success" class="mb-2" @click="downloadChecks()">
          ექსპორტი
        </b-button>
      </div>
    </div>
    <div class="row mt-5">
      <div v-for="(check, i) of checks" :key="i" class="col-12">
        <div class="accordion" role="tablist">
          <b-card no-body class="mb-1">
            <b-card-header header-tag="header" class="p-3 cursor-pointer" role="tab" @click="toggleAccordion(check)">
              {{ check.name }} - {{ moment(check.created_at).format('YYYY-MM-DD HH:mm:ss') }} - {{ check.user.name }}

              <div class="check-actions float-right">
                <b-icon icon="circle-fill"
                        :class="[ 'mr-3', { 'text-success': check.is_visible }, { 'text-danger': !check.is_visible } ]" :title="check.is_visible ? 'დეაქტივაცია' : 'აქტივაცია'"
                />
                <b-icon icon="pencil-square" class="mr-3 text-info" />
                <b-icon icon="archive" class="mr-3 text-warning" @click="copyFromArchive(check)" />
                <b-icon v-if="canDelete(check)" icon="trash" class="text-danger" @click="deleteCheck(check)" />
              </div>
            </b-card-header>
            <b-collapse :id="'accordion-' + i" :visible="visibleAccordions.indexOf(check.id) !== -1" accordion="my-accordion" role="tabpanel" @show="onCheckShow(check)">
              <b-card-body>
                <table class="cheki" style="width: 100%" tableexport-key="tableexport-3">
                  <caption class="btn-toolbar tableexport-caption" style="caption-side: bottom;">
                    <button type="button" tableexport-id="5289c334-xlsx" class="btn btn-default xlsx">
                      Export to xlsx
                    </button>
                  </caption>
                  <tbody>
                    <tr class="head">
                      <td rowspan="2">
                        #
                      </td>
                      <td style="text-align: center;     height: 100px;">
                        ოპერაციის ანგარიშის
                      </td>
                      <td rowspan="2">
                        #
                      </td>
                      <td style="text-align: center;" colspan="4">
                        ვალუტა
                      </td>
                      <td rowspan="2">
                        ბანკი/ სალ
                      </td>
                      <td rowspan="2">
                        მიმღები
                      </td>
                      <td rowspan="2">
                        ბრენდი
                      </td>
                      <td rowspan="2">
                        შესაბამ.საბუთი
                      </td>
                      <td rowspan="2">
                        კომენტარი
                      </td>
                      <td rowspan="2" class="rotate">
                        <div>
                          {{ check.user.name }}
                        </div>
                      </td>
                      <td v-for="manager of check.user.managers" rowspan="2" class="rotate">
                        <div class="rotate">
                          {{ manager.name }}
                        </div>
                      </td>
                      <td rowspan="2">
&nbsp;
                      </td>
                    </tr>
                    <tr class="head">
                      <td style="text-align: center;">
                        დასახელება
                      </td>
                      <td style="width: 70px;">
                        GEL
                      </td>
                      <td style="width: 70px;">
                        EUR
                      </td>
                      <td style="width: 70px;">
                        RUR
                      </td>
                      <td style="width: 70px;">
                        USD
                      </td>
                    </tr>
                    <tr v-for="(item, j) of check.items" :key="j">
                      <td>{{ j }}</td>
                      <td>{{ item.op_name }}</td>
                      <td>{{ item.op_number }}</td>
                      <td>{{ item.gel }}</td>
                      <td>{{ item.eur }}</td>
                      <td>{{ item.rur }}</td>
                      <td>{{ item.usd }}</td>
                      <td>{{ item.source }}</td>
                      <td>{{ item.destination }}</td>
                      <td>{{ item.brand }}</td>
                      <td>{{ item.doc_type }}</td>
                      <td>{{ item.comment }}</td>
                      <td>
                        <!--                        <b-icon v-if="canEdit(check, item)" class="cursor-pointer" icon="pencil" @click="editCheckItem(check, item)" />-->
                        <!--                        <b-icon v-if="canFinish(check, item)" class="cursor-pointer" icon="pencil" @click="finishCheckItem(check, item)" />-->
                      </td>
                      <td v-for="manager of check.user.managers">
                        <!--                        <b-icon :class="getApproveModalIcon(item, manager).class + ' cursor-pointer'" :icon="getApproveModalIcon(item, manager).icon" @click="openApproveModal(check, item, manager)" />-->
                      </td>
                      <td>
                        <!--                        <b-icon icon="trash" class="cursor-pointer text-danger" @click="removeCheckItem(check, item)" />-->
                      </td>
                    </tr>
                    <tr class="edit_tr_130">
                      <td>&nbsp;</td>
                      <td><input v-model="newItem.op_name" class="form-control"></td>
                      <td><input v-model="newItem.op_number" class="form-control"></td>
                      <td><input v-model="newItem.gel" class="form-control"></td>
                      <td><input v-model="newItem.eur" class="form-control"></td>
                      <td><input v-model="newItem.rur" class="form-control"></td>
                      <td><input v-model="newItem.usd" class="form-control"></td>
                      <td><input v-model="newItem.source" class="form-control"></td>
                      <td><input v-model="newItem.destination" class="form-control"></td>
                      <td><input v-model="newItem.brand" class="form-control"></td>
                      <td><input v-model="newItem.doc_type" class="form-control"></td>
                      <td><input v-model="newItem.comment" class="form-control"></td>
                      <td colspan="4">
                        <!--<b-button v-if="!newItem.id" @click="addCheckItem(check)">
                          <b-icon icon="file-earmark-plus" />
                        </b-button>
                        <div v-else>
                          <b-button @click="addCheckItem(check)">
                            <b-icon icon="pencil" />
                          </b-button>
                          <b-button @click="newItem = getEmptyNewItem()">
                            <b-icon icon="x-circle" />
                          </b-button>
                        </div>-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </b-card-body>
            </b-collapse>
          </b-card>
        </div>
      </div>
    </div>

    <div class="row mt-5">
      <div class="col-12">
        <b-pagination
          v-model="currentPage"
          :total-rows="rows"
          :per-page="perPage"
          aria-controls="my-table"
        />
      </div>
    </div>
  </card>
</template>

<script>
import store from '../../store'
import { mapGetters } from 'vuex'
import axios from 'axios'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import { saveAs } from 'file-saver'

export default {
  middleware: 'auth',

  components: { vSelect },

  metaInfo () {
    return { title: 'ჩეკების არქივი' }
  },

  data () {
    return {
      checkToEdit: null,
      visibleAccordions: [],
      newItem: this.getEmptyNewItem(),
      itemToApprove: null,
      checkToApprove: null,
      managerToApprove: null,
      currentPage: 1,
      rows: 0,
      perPage: 50,
      searchParams: {
        dateFrom: '',
        dateTo: '',
        operators: []
      }
    }
  },

  computed: {
    ...mapGetters({
      checks: 'check/archiveChecks',
      operators: 'user/users',
      isAdmin: 'auth/isAdmin'
    })
  },

  watch: {
    currentPage (newValue, oldValue) {
      this.loadChecks()
    }
  },

  mounted () {
    this.loadChecks()

    store.dispatch('user/getAll')
  },

  methods: {
    canDelete (check) {
      return this.isAdmin === true
    },

    deleteCheck (check) {
      axios.delete('/api/checks/' + check.id)
        .then(response => {
          this.loadChecks()
        })
    },
    downloadChecks () {
      saveAs('/api/checks/export')
    },

    getApproveModalIcon (item, manager) {
      let status = item.statuses.filter(o => o.user_id == manager.id)

      if (status.length == 0) return { icon: 'circle', class: 'text-warning' }

      return status[0].is_accepted ? { icon: 'check-circle', class: 'text-success' } : { icon: 'x-circle', class: 'text-danger' }
    },

    getEmptyNewItem () {
      return {
        op_name: '',
        op_number: '',
        gel: '',
        eur: '',
        rur: '',
        usd: '',
        source: '',
        destination: '',
        brand: '',
        doc_type: '',
        comment: ''
      }
    },

    onCheckShow (check) {
      this.loadCheckItems(check)
      this.loadCheckManagers(check)
    },

    loadCheckItems (check) {
      axios.get('/api/checks/' + check.id + '/items')
        .then(response => {
          check.items = response.data.data
        })
    },

    loadCheckManagers (check) {
      axios.get('/api/checks/' + check.id + '/managers')
        .then(response => {
          check.user.managers = response.data.data
        })
    },

    copyFromArchive (check) {
      axios.get('/api/checks/' + check.id + '/copy-from-archive')
        .then(() => {
          this.$router.push({ name: 'checks' })
        })
    },

    toggleAccordion (check) {
      let i = this.visibleAccordions.indexOf(check.id)

      if (i !== -1) this.visibleAccordions.splice(i, 1)
      else this.visibleAccordions.push(check.id)
    },

    checkCreated (check) {
      this.loadChecks()
    },

    checkUpdated (check) {
      this.loadChecks()
    },

    editCheck (check) {
      this.checkToEdit = check
      this.$bvModal.show('check-modal')
    },

    loadChecks () {
      store.dispatch('check/getArchiveChecks', { page: this.currentPage, filter: this.searchParams })
    }
  }
}
</script>

<style scoped>
  td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
  }

  .collapse.show {
    overflow-x: scroll;
  }

  /*.ve {*/
  /*  margin: 100px 0 0 0;*/
  /*  width: 23px;*/
  /*  transform: rotate(-90deg);*/
  /*}*/
</style>
