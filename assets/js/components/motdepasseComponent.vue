<template>
    <div class="col-md-11 col-md-offset-1">

        <div class="row">
            <div class="col-sm-12">
                <h2 class="pull-left project-title">Liste des mots de passe</h2>
                <button class="btn btn-default btn-sm pull-right" data-toggle="modal" data-target="#passwordCreate">Nouveau mot de passe</button>
            </div>
        </div>

        <hr>

        <div v-if="passwords.length > 0">
            <div class="panel panel-default">
                <table class="table table-condensed ">
                    <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Titre</th>
                        <th class="text-center">Url</th>
                        <th class="text-center">Username</th>
                        <th class="text-center">Password</th>
                        <th class="text-center">DernModif</th>
                        <th class="text-center">Update</th>
                        <th class="text-center">Delete</th>
                    </tr>
                    </thead>
                    <tbody name="list" is="transition-group">
                    <tr v-for="pass in passwords" :key="pass.id">
                        <td class="text-right col-md-1">{{ pass.id }}</td>
                        <td class="col-md-2">{{ pass.titre }}</td>
                        <td class="col-md-2">{{ pass.url }}</td>
                        <td class="col-md-2">{{ pass.username }}</td>
                        <td class="col-md-2">{{ pass.password }}</td>
                        <td class="text-center col-md-1"><span class="label" v-bind:class="[isExpired(pass.updatedAt) ? 'label-danger' : 'label-success']">{{ calculInterval(pass.updatedAt) }} jours</span></td>
                        <td class="text-center col-md-1">
                            <button type="button" class="btn btn-info" @click="editPassword({id: pass.id, titre: pass.titre, url: pass.url, username:pass.username, motDePasse:pass.password, note:pass.note})" data-toggle="modal" data-target="#passwordEdit"><span class="glyphicon glyphicon-pencil"></span></button>
                        </td>
                        <td class="text-center col-md-1">
                            <button type="button" class="btn btn-danger" @click="deletePassword(pass.id)"><span class="glyphicon glyphicon-trash"></span></button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else>
            <h3 align="center">Aucun mot de passe enregistré</h3>
        </div>
        <!-- New Password Modal -->
        <div class="modal fade" id="passwordCreate" role="dialog">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" @click="razNewPassword">×</button>
                        <h4 class="modal-title">Nouveau mot de passe</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-horizontal row-border">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Titre</label>
                                <div class="col-md-10">
                                    <input v-model="newPassword.titre" type="text" class="form-control" id="titre-password" placeholder="Titre">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Url</label>
                                <div class="col-md-10">
                                    <input v-model="newPassword.url" type="text" class="form-control" id="url" placeholder="Url">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Username</label>
                                <div class="col-md-10">
                                    <input v-model="newPassword.username" type="text" class="form-control" id="username" placeholder="Username">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Mot de passe</label>
                                <div class="col-md-8">
                                    <input v-model="newPassword.motDePasse" type="text" class="form-control" id="motDePasse" placeholder="Mot de passe">
                                </div>
                                <div class="col-md-2">
                                    <button @click="generatePassword('new')" class="btn btn-default btn-success">Random</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Note</label>
                                <div class="col-md-10">
                                    <textarea v-model="newPassword.note" class="form-control" id="note" placeholder="note"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="col-md-12">
                            <button data-dismiss="modal" v-bind:disabled="isDisabled" @click="createNewMotDePasse" type="submit" class="btn btn-default btn-success">Ajouter</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Password Modal -->
        <div class="modal fade" id="passwordEdit" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" @click="razEditedPassword">×</button>
                        <h4 class="modal-title">Modif d'un mot de passe</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-horizontal row-border">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Id</label>
                                <div class="col-md-10">
                                    <input v-model="passwordEdited.id" type="text" class="form-control" id="edit-id" placeholder="Id" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Titre</label>
                                <div class="col-md-10">
                                    <input v-model="passwordEdited.titre" type="text" class="form-control" id="edit-titre-password" placeholder="Titre">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Url</label>
                                <div class="col-md-10">
                                    <input v-model="passwordEdited.url" type="text" class="form-control" id="edit-url" placeholder="Url">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Username</label>
                                <div class="col-md-10">
                                    <input v-model="passwordEdited.username" type="text" class="form-control" id="edit-username" placeholder="Username">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Mot de passe</label>
                                <div class="col-md-8">
                                    <input v-model="passwordEdited.motDePasse" type="text" class="form-control" id="edit-motDePasse" placeholder="Mot de passe">
                                </div>
                                <div class="col-md-2">
                                    <button @click="generatePassword('edit')" class="btn btn-default btn-success">Random</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Note</label>
                                <div class="col-md-10">
                                    <textarea v-model="passwordEdited.note" class="form-control" id="edit-note" placeholder="note"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button data-dismiss="modal" v-bind:disabled="isDisabled" @click="savePassword" type="submit" class="btn btn-default btn-success">Modifier</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import moment from 'moment'

    export default {
        data: function () {
            return {
                passwords: {},
                newPassword: {},
                passwordEdited: {}
            }
        },
        created() {
            axios.get('/passwords').then(res => {
                this.passwords = res.data;
            })
        },
        computed: {
            isDisabled() {
                return this.passwords.titre === '';
            }
        },
        methods: {
            calculInterval: function (value) {
                if (!value) return '';
                let todaysDate = moment(new Date());
                let oDate = moment(new Date(value.timestamp * 1000));
                return todaysDate.diff(oDate, 'days');

            },
            isExpired(value) {
                if (this.calculInterval(value) > 300) {
                    return true;
                } else {
                    return false;
                }
                ;
            },

            /**
             * Create a new motdepasse.
             */
            createNewMotDePasse() {
                axios.post('/motdepasse/new', {
                    titre: this.newPassword.titre,
                    url: this.newPassword.url,
                    username: this.newPassword.username,
                    motDePasse: this.newPassword.motDePasse,
                    note: this.newPassword.note
                }).then(res => {
                    this.passwords.push(res.data)
                    //Reinit form
                    this.razNewPassword()
                })
            },

            /**
             * Edit motdepasse
             */
            editPassword(item) {
                this.passwordEdited = item
            },

            /**
             * Remise à 0 du formulaire d'edition
             */
            razEditedPassword(){
                this.passwordEdited = {}
            },

            /**
             * Remise à 0 du formulaire d'edition
             */
            razNewPassword(){
                this.newPassword = {}
            },

            /**
             * Save edit motdepasse
             */
            savePassword() {
                //On envoie la modif à Symfony
                axios.post('/motdepasse/save/' + parseInt(this.passwordEdited.id), {
                    titre: this.passwordEdited.titre,
                    url: this.passwordEdited.url,
                    username: this.passwordEdited.username,
                    motDePasse: this.passwordEdited.motDePasse,
                    note: this.passwordEdited.note
                }).then(res => {
                    //On modifie la ligne avec les nouvelles données
                    let idChanged = this.passwordEdited.id;
                    this.passwords.forEach(function (element) {
                        if (element.id === idChanged) {
                            element.username = res.data.username;
                            element.password = res.data.password;
                            element.titre = res.data.titre;
                            element.url = res.data.url;
                        }
                    });
                    this.razEditedPassword()
                })
            },

            /**
             * Génération d'un mot de passe aleatoire
             */
            generatePassword(action) {
                axios.get('/motdepasse/generate').then(res => {
                    if (action === 'edit') {
                        this.$set(this.passwordEdited,'motDePasse',res.data);
                    } else {
                        this.$set(this.newPassword, 'motDePasse', res.data);
                    }
                })
            },

            /**
             * Suppression d'un mot de passe
             */
            deletePassword(id) {
                axios.get('/motdepasse/delete/' + parseInt(id)).then(res => {
                    if (res.data === 'ok') {
                        //On supprime le mot de passe du tableau
                        let idDeleted = id;
                        let cpt = 0;
                        let rowASupprimer = -1;
                        this.passwords.forEach(function (element) {
                            if (element.id === idDeleted) {
                                rowASupprimer = cpt;
                            }
                            cpt++;
                        });
                        if (rowASupprimer > -1) {
                            this.passwords.splice(rowASupprimer, 1);
                        }
                    }
                })
            }
        },
    }
</script>
