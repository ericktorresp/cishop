from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User

class Weapon(models.Model):
    title = models.CharField(max_length=100)
    photo = FileBrowseField(u'Photo', max_length=200, directory="weapon/", format="Image", extensions=['.jpg', '.gif', '.png'])
    price = models.IntegerField()
    damage_min = models.SmallIntegerField()
    damage_max = models.SmallIntegerField()
    skill = models.SmallIntegerField()
    proficiency = models.SmallIntegerField()
    type = models.CharField(max_length=20, choices=(('Melee', 'Melee'), ('Rifle', 'Rifle'), ('Handgun', 'Handgun'), ('Heavy', 'Heavy')))
    durability = models.SmallIntegerField()
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    user_weapon = models.ManyToManyField(User, through='UserWeapon')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'

class UserWeapon(models.Model):
    user = models.ForeignKey(User)
    weapon = models.ForeignKey(Weapon)
    actived = models.BooleanField(default=False)
    used = models.IntegerField(default=0, blank=True, null=True)
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    modified = models.DateTimeField('Updated', editable=False, auto_now=True)
    
    def __unicode__(self):
        return self.user.username + "'s " + self.weapon.title

    class Meta:
        db_table = 'user_weapon'
        app_label = 'system'
