/**
 * Objekt zum Authentifizieren des Benutzers und der Verwaltung dessen Rechten
 * 
 * @author thorsten
 */
MySched.Authorize = function ()
{
    var user, authWindow, authentificatedToken, accArr, role, additionalRights;

    return {
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
         * Setzt die Zusaetzlichen Rechte (zu den RollenRechten) fest
         * 
         * @param {Object}
         *            rights
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
         * Aendert die Rolle des aktuellen Users (zb. beim Anmelden von standard
         * auf spiezielle) Hierbei wird automatisch der Baum neugeladen falls
         * die Rolle sich aendert
         * 
         * @param {Object}
         *            role
         * @param {Object}
         *            rights
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
        // Verifiziere das Token bei dem Server
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
                    try
                    {
                        var json = Ext.decode(response.responseText);
                        if (json.success)
                        {
                            this.authentificatedToken = t;
                            success.call(scope, json); // uebergebene
                            // Callback
                            // ausfuehren
                        }
                        else
                        {
                            this.authentificatedToken = null;
                            if (json.errors.reason)
                            {
                                MySched.CookieProvider.clear('authToken');
                                Ext.Msg.alert(
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_AUTHORIZE_FAILED,
                                MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_AUTHORIZE_FAILED_MSG1 + json.errors.reason + MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_AUTHORIZE_FAILED_MSG2,
                                this.showAuthForm(),
                                this);
                            }

                        }
                    }
                    catch (e)
                    {
                        Ext.Msg.alert(
                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_AUTHORIZE_FAILED,
                        MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_AUTHORIZE_FAILED_MSG3,
                        this.showAuthForm(), this);
                    }
                },
                scope: scope || this
            });

        },
        /**
         * Laed den gespeicherten Userstundenplan
         */
        loadUserSchedule: function ()
        {
            MySched.Base.loadUserSchedule();
        },
        // die Verifizierung war erfolgreich
        verifySuccess: function (obj)
        {
            MySched.Base.sid = obj.sid;
            this.user = obj.username;
            this.role = obj.role;
            MySched.Authorize.changeRole(obj.role, obj.additional_rights);

            Ext.ComponentMgr.get('btnSave').show();

            if (obj.role !== "registered")
            {
                if (typeof Ext.ComponentMgr.get('btnEvent') !== "undefined")
                {
                    Ext.ComponentMgr.get('btnEvent')
                        .show();
                }
            }

            // Erstellt den Stundenplan des Benutzers
            MySched.Base.createUserSchedule();
            MySched.Authorize.loadUserSchedule();

            MySched.layout.viewport.doLayout();
            MySched.selectedSchedule.responsible = this.user;
            MySched.selectedSchedule.status = "saved";
        },
        /**
         * Ein Vorcheck, ob fuer einen bestimmten Typ ueberhaupt Berechtigungen
         * vorliegen oder nicht gibt 'none' -> Keinerlei Rechte, 'part' ->
         * Partielle Rechte oder 'full' -> Volle Rechte zurueck
         * 
         * @param {Object}
         *            type
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
         * Zeigt das Authentifizierungsfenster an Gibt true zurueck wenn der
         * User bereits Authentifiziert ist
         */
        showAuthForm: function (funcAfterAuth)
        {
            if (this.user && MySched.Base.sid)
            {
                return true;
            }

            // Funktion die nach dem Auth ausgefuehrt wird
            this.afterAuthCallback = funcAfterAuth;

            // Eventuelle Fortschrittsdialoge ausblenden
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
        saveIfAuth: function (showWindow)
        {
            var task = "";
            if (MySched.selectedSchedule.id === "mySchedule")
            {
                task = "UserSchedule.save";
            }
            else
            {
                task = "saveScheduleChanges";
            }
            MySched.selectedSchedule.save.call(MySched.selectedSchedule,
            _C('ajaxHandler'), showWindow, task);
        },
        isClassSemesterAuthor: function ()
        {
            if (this.user === MySched.class_semester_author)
            {
                return true;
            }
            return false;
        }
    };
}();