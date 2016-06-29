/**
 * Object to authenticate the user und manage his rights
 *
 * @author thorsten
 * @class MySched.Authorize
 */
MySched.Authorize = function ()
{
    var user, authWindow, authentificatedToken, accArr, role, additionalRights;

    return {
        /**
         * Initialize the rights of the user
         *
         * @method init
         * @param {object} accessArray Inforamtion above the rights of the user
         */
        init: function (accessArray)
        {
            this.additionalRights = {};
            this.accArr = accessArray;
            if (!this.role)
            {
                this.role = accessArray.defaultRole;
            }
        },
        /**
         * Sets additional rights (additional to the role rights) Setzt die Zusaetzlichen Rechte (zu den RollenRechten) fest
         *
         * @method setAdditionalRights
         * @param {Object} rights Information above additional rights
         * @return {boolean} * False if the user has no additional rights otherwise true
         */
        setAdditionalRights: function (rights)
        {
            if (this.additionalRights === rights)
            {
                return false;
            }
            this.additionalRights = rights;
            return true;
        },
        /**
         * Changes the role of the active user (e.g. on the logon from standard to special). Therefore the tree will
         * be reload if the role changed.
         *
         * @method changeRole
         * @param {number} role TODO: It is a number. I don't know what it stand for
         * @param {Object} rights Information above additional rights
         * @return {boolean} * True of the roles changed otherwise false.
         */
        changeRole: function (role, rights)
        {
            if (!this.setAdditionalRights(rights) && this.role === role)
            {
                return false;
            }
            this.role = role;
            return true;
        },
        /**
         * Verify the token from the server
         *
         * @method verifyToken
         * @param {string} t The token
         * @param {function} success The function which should proceed if the request was successfully
         * @param {object} scope Information above the user
         * @return {boolean} * True if the t is already authenticated
         */
        verifyToken: function (t, success, scope)
        {
            if (t === this.authentificatedToken)
            {
                return true;
            }
            Ext.Ajax.request(
                {
                    url: _C('ajaxHandler'),
                    method: 'POST',
                    params: {
                        token: t,
                        scheduletask: "User.auth"
                    },
                    success: function (response, request)
                    {

                        var json = Ext.decode(response.responseText);
                        if (json.success)
                        {
                            this.authentificatedToken = t;
                            success.call(scope, json);
                            // run given callback
                        }
                        else
                        {
                            this.authentificatedToken = null;
                            if (json.errors.reason)
                            {
                                MySched.CookieProvider.clear('authToken');
                                Ext.Msg.alert(
                                    MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_AUTHORIZE_FAILED,
                                    MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_AUTHORIZE_FAILED_MSG1 + json.errors.reason + MySchedLanguage.COM_THM_ORGANIZER_MESSAGE_SCHEDULER_AUTHORIZE_FAILED_MSG2,
                                    this.showAuthForm(),
                                    this);
                            }

                        }

                    },
                    scope: scope || this
                });

        },
        /**
         * Loads the saved user schedule
         *
         * @method loadUserSchedule
         */
        loadUserSchedule: function ()
        {
            MySched.Base.loadUserSchedule();
        },
        /**
         * Verfiying was successful and the schedule will be created for the user.
         *
         * @method verifySuccess
         * @param {object} obj Information above user and the token
         */
        verifySuccess: function (obj)
        {
            MySched.Base.sid = obj.sid;
            this.user = obj.username;
            this.role = obj.role;
            MySched.Authorize.changeRole(obj.role, obj.additional_rights);

            Ext.ComponentMgr.get('btnSave').show();

            // Creating the schedule for the user
            MySched.Base.createUserSchedule();

            MySched.Schedule.on("dataLoaded", function ()
            {
                MySched.Authorize.loadUserSchedule();
            });

            MySched.layout.viewport.doLayout();
            MySched.selectedSchedule.responsible = this.user;

            MySched.Schedule.status = "saved";
        },
        /**
         * TODO: I think it is never used and obsolete
         * Ein Vorcheck, ob fuer einen bestimmten Typ ueberhaupt Berechtigungen
         * vorliegen oder nicht gibt 'none' -> Keinerlei Rechte, 'part' ->
         * Partielle Rechte oder 'full' -> Volle Rechte zurueck
         *
         * @param {Object} type
         * @return {boolean} *
         */
        checkAccessMode: function (type)
        {
            var part = false;
            // Ueberprueft vorkommen in ALL und dann in type - entweder muss *
            // oder id vorkommen
            if (this.accArr.ALL[type])
            {
                if (this.accArr.ALL[type] === '*')
                {
                    return 'full';
                }
                part = true;
            }
            if (this.accArr[this.role][type])
            {
                if (this.accArr[this.role][type] === '*')
                {
                    return 'full';
                }
                part = true;
            }
            if (this.additionalRights[type])
            {
                if (this.additionalRights[type] === '*')
                {
                    return 'full';
                }
                part = true;
            }

            return part ? 'part' : 'none';
        },
        // Ueberprueft ob der Zugriff auf dieses Objekt erlaubt ist
        /**
         * TODO: I think it is never used and obsolete
         *
         * @param type
         * @param id
         * @return {boolean}
         */
        checkAccess: function (type, id)
        {
            if (Ext.isEmpty(id))
            {
                id = 'keyKommtBestimmtNichtVor';
            }
            id = id.toLowerCase();

            // Ueberprueft vorkommen in ALL und dann in type - entweder muss *
            // oder id vorkommen
            if (this.accArr.ALL[type])
            {
                if (this.accArr.ALL[type] === '*' || this.accArr.ALL[type].indexOf(id) !== -1)
                {
                    return true;
                }
            }

            // SPezifische Rollenrechte
            if (this.accArr[this.role][type])
            {
                if (this.accArr[this.role][type] === '*' || this.accArr[this.role][type].indexOf(id) !== -1)
                {
                    return true;
                }
            }

            // Persoenliche Userrechte
            if (this.additionalRights[type])
            {
                if (this.additionalRights[type] === '*' || this.additionalRights[type].indexOf(id) !== -1)
                {
                    return true;
                }
            }

            return false;
        },
        // Schaut nach ob im Cookie ein Token enthalten ist und veranlasst eine
        // Pruefung
        /**
         * TODO: I think it is never used and obsolete
         *
         * @return {boolean}
         */
        checkCookieToken: function ()
        {
            var token = MySched.CookieProvider.get('authToken');
            if (token)
            {
                this.verifyToken(token, this.verifySuccess, this);
                return true;
            }
            return false;
        },
        /**
         * Shows the authentication window and gives back true if the user is already authenticated
         * TODO: not sure if the is in use anymore, but a call can be found in verifyToken
         *
         * @method showAuthForm
         * @param funcAfterAuth
         * @return {boolean} *
         */
        showAuthForm: function (funcAfterAuth)
        {
            if (this.user && MySched.Base.sid)
            {
                return true;
            }

            // function is called after auth
            this.afterAuthCallback = funcAfterAuth;

            // possible fade out the progress bar
            if (Ext.Msg.isVisible())
            {
                Ext.Msg.hide();
            }

            if (this.authWindow)
            {
                this.authWindow.show();
            }

            return false;
        },
        /**
         * This method determines the task for the saving.
         * TODO: This is in use but maybe obsolete.
         *
         * @method saveIfAuth
         * @param {object} showWindow The window object which is shown when the schedule is saved
         */
        saveIfAuth: function (showWindow)
        {
            var task = "";
            // TODO: This doesn't make any sense to me, because of the line after the if clause
            if (MySched.selectedSchedule.id === "mySchedule")
            {
                task = "UserSchedule.save";
            }
            else
            {
                task = "saveScheduleChanges";
            }

            // Saving always refers to "My schedule"
            task = "UserSchedule.save";

            MySched.selectedSchedule.save.call(MySched.selectedSchedule, _C('ajaxHandler'), showWindow, task);
        }
    };
}();