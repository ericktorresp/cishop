from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _
from system.models import Character

IN_WHERE = (
    ('rip', _('rip')),
    ('prison', _('prison')),
)

class UserData(models.Model):
    """
    IN_WHERE
    * null
    * rip:
    * prison:
    """
    user = models.ForeignKey(User, verbose_name=_('user'), unique=True)
    respect = models.FloatField(_('respect'), default=0)
    spirit = models.SmallIntegerField(_('spirit'), default=0)
    intelligence = models.FloatField(_('intelligence'), default=0)
    strength = models.FloatField(_('strength'), default=0)
    charisma = models.FloatField(_('charisma'), default=0)
    tolerance = models.FloatField(_('tolerance'), default=0)
    stamina = models.SmallIntegerField(_('stamina'), default=0)
    cash = models.IntegerField(_('cash'), default=0)
    credits = models.IntegerField(_('credits'), default=0)
    kills = models.SmallIntegerField(_('kills'), default=0)
    tickets = models.SmallIntegerField(_('tickets'), default=0)
    skill = models.CharField(_('skill'), max_length=100)#list or dict? 4 kinds weapon skill
    proficiency = models.CharField(_('proficiency'), max_length=100)#list or dict? 4 kinds weapon proficiency
    new_status = models.CharField(_('new status'), max_length=100)#list or dict? fella | message | gang status
    stamina_start_time = models.DateTimeField(_('stamina charge begin at'), blank=True, null=True)
    drug_deals = models.SmallIntegerField(_('drug deals'), default=0)
    yen = models.SmallIntegerField(_('yen'), default=0)
    in_where = models.CharField(_('in where?'), choices=IN_WHERE, blank=True, null=True, max_length=10)
    release_time = models.DateTimeField(_('release time'), blank=True, null=True)
    change_character = models.ForeignKey(Character, blank=True, null=True, verbose_name=_('change character'))
    change_time = models.DateTimeField(_('change time'), blank=True, null=True)

    def __unicode__(self):
        return _("%s's game data.") % self.user.username

    def delete(self):
        if self.user:
            return False
        super(UserData, self).delete()
    
    class Meta:
        app_label = 'account'
        verbose_name = _('game data')
        verbose_name_plural = _('game datas')
        db_table = 'user_data'
