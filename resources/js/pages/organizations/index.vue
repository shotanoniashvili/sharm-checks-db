<template>
  <card>
    <div class="card-title">
      ორგანიზაციები
      <b-button v-b-modal.organization-modal class="float-right">
        <b-icon icon="plus-circle" aria-hidden="true" />
      </b-button>
    </div>
    <b-table
      :bordered="true"
      :hover="true"
      :items="organizations"
      :fields="fields"
    >
      <!--<template #cell(organizations)="data">
        {{ data.value.map(o => o.name).join(', ') }}
      </template>
      <template #cell(roles)="data">
        {{ data.value.map((i) => i.name).join(', ') }}
      </template>-->
      <template #cell(actions)="data">
        <b-button @click="edit(data.item)">
          <b-icon icon="pencil-square" aria-hidden="true" />
        </b-button>
        <b-button @click="delete(data.item)">
          <b-icon icon="trash" aria-hidden="true" />
        </b-button>
      </template>
    </b-table>
    <organization-modal :item="organizationToEdit" @created="organizationCreated" @updated="organizationUpdated" @hide-modal="organizationToEdit = null" />
  </card>
</template>

<script>
import axios from 'axios'
import OrganizationModal from '../../modals/organization-modal'

export default {
  middleware: 'auth',
  components: { OrganizationModal },

  metaInfo () {
    return { title: 'ორგანიზაციები' }
  },

  data () {
    return {
      organizationToEdit: null,
      organizations: [],
      fields: [
        {
          key: 'name',
          label: 'სახელი'
        },
        {
          key: 'actions',
          label: 'მოქმედებები'
        }
      ]
    }
  },

  mounted () {
    this.loadData()
  },

  methods: {
    organizationCreated () {
      this.loadData()
    },

    organizationUpdated () {
      this.loadData()
    },

    edit (organization) {
      this.organizationToEdit = organization
      this.$bvModal.show('organization-modal')
    },

    delete (organization) {
      axios.delete('/api/organizations/' + organization.id)
        .then(() => {
          this.loadData()
        })
    },

    loadData () {
      axios.get('/api/organizations')
        .then(response => {
          this.organizations = response.data.data
        })
    }
  }
}
</script>
