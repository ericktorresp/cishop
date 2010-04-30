from game.models import *
from django.contrib import admin

class MemberInline(admin.StackedInline):
    model = GangMember
    extra = 5
    raw_id_fields = ['user']
    classes = ('collapse-open',)
    
class GangAdmin(admin.ModelAdmin):
    inlines = [MemberInline]
    raw_id_fields = ['creater', 'leader', 'vice_leader', ]
    list_display = ('title', 'creater', 'leader', 'vice_leader', 'created',)

    
admin.site.register(Gang, GangAdmin)
admin.site.register(GangNews)
admin.site.register(GangInvite)
