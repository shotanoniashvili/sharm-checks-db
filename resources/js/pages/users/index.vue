<template>
  <card>
    <div class="card-title">
      მომხმარებლები
      <b-button v-b-modal.user-modal class="float-right">
        <b-icon icon="person-plus-fill" aria-hidden="true" />
      </b-button>
    </div>
    <b-table
      :bordered="true"
      :hover="true"
      :items="users"
      :fields="fields"
    >
      <template #cell(organization)="data">
        {{ data.value.name }}
      </template>
      <template #cell(roles)="data">
        {{ data.value.map((i) => i.name).join(', ') }}
      </template>
      <template #cell(actions)="data">
        <b-button @click="editUser(data.item)">
          <b-icon icon="pencil-square" aria-hidden="true" />
        </b-button>
        <b-button @click="deleteUser(data.item)">
          <b-icon icon="trash" aria-hidden="true" />
        </b-button>
      </template>
    </b-table>
    <user-modal :item="userToEdit" @user-created="userCreated" @user-updated="userUpdated" @hide-modal="userToEdit = null" />
  </card>
</template>

<script>
import axios from 'axios'
import UserModal from '../../modals/user-modal'

export default {
  middleware: 'auth',
  components: { UserModal },

  metaInfo () {
    return { title: 'მომხმარებლები' }
  },

  data () {
    return {
      userToEdit: null,
      users: [],
      fields: [
        {
          key: 'name',
          label: 'სახელი'
        },
        {
          key: 'email',
          label: 'ელ-ფოსტა'
        },
        {
          key: 'organization',
          label: 'ორგანიზაცია'
        },
        {
          key: 'roles',
          label: 'მოხმარებლის ტიპი'
        },
        {
          key: 'actions',
          label: 'მოქმედებები'
        }
      ]
    }
  },

  mounted () {
    this.loadUsers()
  },

  methods: {
    userCreated (user) {
      this.loadUsers()
    },

    userUpdated (user) {
      this.loadUsers()
    },

    editUser (user) {
      this.userToEdit = user
      this.$bvModal.show('user-modal')
    },

    deleteUser (user) {
      axios.delete('/api/users/' + user.id)
        .then(() => {
          this.loadUsers()
        })
    },

    loadUsers () {
      axios.get('/api/users')
        .then(response => {
          this.users = response.data.data
        })
    }
  }
}
</script>
