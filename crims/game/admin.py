from game.models import *
from system.models.armor import UserArmor
from django.contrib import admin
from django.contrib.auth.admin import UserAdmin

class MemberInline(admin.StackedInline):
    model = GangMember
    extra = 5
    raw_id_fields = ['user']
    classes = ('collapse-open',)
    
class GangAdmin(admin.ModelAdmin):
    inlines = [MemberInline]
    raw_id_fields = ['creater', 'leader', 'vice_leader', ]
    list_display = ('title', 'creater', 'leader', 'vice_leader', 'created',)

class BountyAdmin(admin.ModelAdmin):
    raw_id_fields = ['sponsor', 'target']
    list_display = ('sponsor', 'target', 'credits', 'expired', 'completed',)

class BankInline(admin.StackedInline):
    model = Bank
    fk_name = 'user'
    can_delete = False

class ArmorInline(admin.StackedInline):
    model = UserArmor
    fk_name = 'user'
    can_delete = False

class BankAdmin(UserAdmin):
    inlines = (BankInline, ArmorInline,)
        
admin.site.register(Gang, GangAdmin)
admin.site.register(GangNews)
admin.site.register(GangInvite)
admin.site.register(Bounty, BountyAdmin)
admin.site.unregister(User)
admin.site.register(User, BankAdmin)
