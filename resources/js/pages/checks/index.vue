<template>
  <card>
    <div class="row mb-5">
      <div class="col-12">
        <div class="card-title">
          ჩეკები
          <b-button v-b-modal.check-modal class="float-right">
            <b-icon icon="plus-circle" aria-hidden="true" />
          </b-button>
        </div>
      </div>
    </div>
    <div class="row mt-5">
      <div v-for="(check, i) of checks" :key="i" class="col-12">
        <div class="accordion" role="tablist">
          <b-card no-body class="mb-1">
            <b-card-header header-tag="header" class="p-3 cursor-pointer" role="tab" @click="toggleAccordion(check)">
              <div class="float-left">
                {{ check.name }} - {{ moment(check.created_at).format('YYYY-MM-DD HH:mm:ss') }} - {{ check.user.name }}
                <span
                  class="position-relative"
                  v-for="item of check.items"
                  :key="item.id">
                  <b-icon
                    icon="circle-fill"
                    :class="[ 'ml-3', { 'text-success': item.is_approved }, { 'text-warning': !item.is_approved } ]"
                  />
                  <span class="badge badge-item-status" v-if="!item.is_approved">{{ item.statuses.length }}</span>
                </span>
                <b-icon icon="cash-stack" :class="['ml-3', { 'text-success': check.is_paid }, { 'text-danger': !check.is_paid }]" />
                <b-icon icon="check2-all" :class="['ml-3', { 'text-success': check.is_finished }, { 'text-danger': !check.is_finished }]" />
              </div>

              <div class="check-actions float-right">
                <b-icon icon="circle-fill"
                        :class="[ 'mr-3', { 'text-success': check.is_visible }, { 'text-danger': !check.is_visible } ]" :title="check.is_visible ? 'დეაქტივაცია' : 'აქტივაცია'"
                        @click="toggleCheckActivation(check)"
                />
                <b-icon icon="pencil-square" class="mr-3 text-info" @click="editCheck(check)" />
                <b-icon v-if="canArchive(check)" icon="archive" class="mr-3 text-warning" @click="moveToArchive(check)" />
                <b-icon v-if="canDelete(check)" icon="trash" class="text-danger" @click="deleteCheck(check)" />
              </div>
            </b-card-header>
            <b-collapse :id="'accordion-' + i" :visible="visibleAccordions.indexOf(check.id) !== -1" accordion="my-accordion" role="tabpanel" @show="onCheckShow(check)">
              <b-card-body>
                <table class="cheki" style="width: 100%">
                  <b-button variant="success" class="mb-2" @click="downloadCheck(check)">
                    ექსპორტი
                  </b-button>
                  <tbody>
                    <tr class="head">
                      <!--                      <td rowspan="2">-->
                      <!--                        #-->
                      <!--                      </td>-->
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
                        <div>
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
                      <!--                      <td>{{ j }}</td>-->
                      <td>{{ item.op_name }}</td>
                      <td><a v-if="item.file" :href="item.file"><b-icon icon="download" /></a></td>
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
                        <b-icon v-if="canEdit(check, item)" class="cursor-pointer text-info" icon="pencil" @click="editCheckItem(check, item)" />
                        <div v-else-if="canFinish">
                          <span v-if="item.is_finished" v-b-tooltip.hover :title="item.finished_by ? item.finished_by.name : ''">
                            <b-icon class="cursor-pointer text-success" icon="check-circle-fill" />
                          </span>
                          <b-icon v-if="!item.is_finished" class="cursor-pointer text-success" icon="check-circle" @click="finishCheckItem(check, item)" />
                        </div>
                      </td>
                      <td v-for="manager of check.user.managers">
                        <b-icon :class="getApproveModalIcon(item, manager).class + ' cursor-pointer'" :icon="getApproveModalIcon(item, manager).icon" @click="openApproveModal(check, item, manager)" />
                        <span v-if="getItemComment(item, manager) && getItemComment(item, manager).comment !== null">
                          <b-badge pill variant="info" class="position-absolute">
                            {{ getItemComment(item, manager).comment.length }}
                          </b-badge>
                        </span>
                      </td>
                      <td>
                        <b-icon v-if="canEdit(check, item)" icon="trash" class="cursor-pointer text-danger" @click="removeCheckItem(check, item)" />
                      </td>
                    </tr>
                    <tr class="edit_tr_130">
                      <!--                      <td>&nbsp;</td>-->
                      <td><input v-model="newItem.op_name" class="form-control"></td>
                      <td>
                        <input id="file-input" type="file" class="d-none" @input="onFileInput($event.target.files)">
                        <b-button variant="info" @click="openFileWindow">
                          ფაილი
                        </b-button>
                      </td>
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
                        <b-button v-if="!newItem.id" @click="addCheckItem(check)">
                          <b-icon icon="file-earmark-plus" />
                        </b-button>
                        <div v-else>
                          <b-button @click="addCheckItem(check)">
                            <b-icon icon="pencil" />
                          </b-button>
                          <b-button @click="newItem = getEmptyNewItem()">
                            <b-icon icon="x-circle" />
                          </b-button>
                        </div>
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

    <check-modal :item="checkToEdit" @check-created="checkCreated" @check-updated="checkUpdated" @hide-modal="checkToEdit = null" />
    <item-approve-modal
      :check="checkToApprove"
      :item="itemToApprove"
      :manager="managerToApprove"
      @hide-modal="onApproveModalHide"
    />
  </card>
</template>

<script>
import CheckModal from '../../modals/check-modal'
import ItemApproveModal from '../../modals/item-approve-modal'
import store from '../../store'
import { mapGetters } from 'vuex'
import axios from 'axios'
import { saveAs } from 'file-saver'

// v. sadzaglishvili ID: 3

export default {
  middleware: 'auth',
  components: { CheckModal, ItemApproveModal },

  metaInfo () {
    return { title: 'ჩეკები' }
  },

  data () {
    return {
      checkToEdit: null,
      visibleAccordions: [],
      newItem: this.getEmptyNewItem(),
      itemToApprove: null,
      checkToApprove: null,
      managerToApprove: null,
      form: new FormData()
    }
  },

  computed: {
    ...mapGetters({
      checks: 'check/activeChecks',
      user: 'auth/user',
      isManager: 'auth/isManager',
      isAdmin: 'auth/isAdmin',
      isOperator: 'auth/isOperator'
    })
  },

  mounted () {
    this.loadChecks()
  },

  methods: {
    onFileInput (fileList) {
      if (!fileList.length) return

      this.form.delete('file')
      this.form.append('file', fileList[0])
    },

    openFileWindow () {
      document.getElementById('file-input').click()
    },

    downloadCheck (check) {
      saveAs('/api/checks/' + check.id + '/export')
    },

    getItemComment (item, manager) {
      if (item.statuses.filter(o => o.user_id == manager.id).length === 0) return null

      return item.statuses.filter(o => o.user_id == manager.id)[0]
    },

    toggleCheckActivation (check) {
      axios.get('/api/checks/' + check.id + '/toggle-status')
        .then(() => {
          check.is_visible = !check.is_visible
        })
    },

    canFinish (check, item) {
      const acceptedByFounder = item.statuses.filter(o => o.user_id === 3)

      if (this.isAdmin || (acceptedByFounder.length > 0 && acceptedByFounder.is_accepted)) return true

      return check.user.managers.length === item.statuses.length
    },

    finishCheckItem (check, item) {
      if (!this.canFinish(check, item)) return

      axios.get('/api/checks/' + check.id + '/items/' + item.id + '/finish')
        .then(() => {
          item.is_finished = true
        })
    },

    canEdit (check, item) {
      if (this.isAdmin) return true

      return item.statuses.length === 0 && check.user_id === this.user.id
    },

    canDelete (check) {
      return this.isAdmin === true
    },

    deleteCheck (check) {
      axios.delete('/api/checks/' + check.id)
        .then(response => {
          this.loadChecks()
        })
    },

    canArchive (check) {
      if (this.isAdmin) return true

      return check.is_finished && check.user_id === this.user.id
    },

    onApproveModalHide () {
      this.loadCheckItems(this.checkToApprove)

      this.checkToApprove = null
      this.itemToApprove = null
      this.managerToApprove = null
    },

    getApproveModalIcon (item, manager) {
      let status = item.statuses.filter(o => o.user_id == manager.id)

      if (status.length == 0) return { icon: 'circle', class: 'text-warning' }

      return status[0].is_accepted ? { icon: 'check-circle', class: 'text-success' } : { icon: 'x-circle', class: 'text-danger' }
    },

    addCheckItem (check) {
      for (let key in this.newItem) {
        if (this.newItem[key]) this.form.append(key, this.newItem[key])
      }

      if (!this.newItem.id) {
        axios.post('/api/checks/' + check.id + '/items', this.form, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
          .then(response => {
            this.newItem = this.getEmptyNewItem()
            this.loadCheckItems(check)
            this.form = new FormData()
          })
      } else {
        axios.post('/api/checks/' + check.id + '/items/' + this.newItem.id, this.form, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
          .then(response => {
            this.newItem = this.getEmptyNewItem()
            this.loadCheckItems(check)
            this.form = new FormData()
          })
      }
    },

    editCheckItem (check, item) {
      this.newItem = item
    },

    openApproveModal (check, item, manager) {
      this.checkToApprove = check
      this.itemToApprove = item
      this.managerToApprove = manager

      this.$bvModal.show('item-approve-modal')
    },

    removeCheckItem (check, item) {
      axios.delete('/api/checks/' + check.id + '/items/' + item.id)
        .then(response => {
          this.loadCheckItems(check)
        })
    },

    getEmptyNewItem () {
      return {
        op_name: '',
        file: '',
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

    moveToArchive (check) {
      axios.get('/api/checks/' + check.id + '/move-to-archive')
        .then(() => {
          this.loadChecks()
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
      store.dispatch('check/getActiveChecks')
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
