<?php
namespace MGameBase;

use MGameBase\MGameBase;

class Language{
	public static  $message=[
	"ch" => array(
	"lv" => "级别",
	"exp" => "经验值",
	"coin" => "金币",
	"account" => "账户",
	"name" => "名称",
	"ad.vip" => "§3[§7充值§cVIP§3]",
	"register.error.unknow" => "§c当你注册时服务器触发了未知错误",
	"login.error.unknow" => "§c当你登陆时服务器触发了未知错误",
	"auth.type.password" => "§a输入你的密码来完成验证", 
	"register.success" => "§a注册成功",
	"login.error.password" => "§3输入的密码不正确",
	"login.success" => "§a登入成功",
	"back.lobby" => "§6返回到大厅",
	"show" => "§a已显示其他玩家",
	"hide" => "§2已屏蔽其他玩家",
	"lang.change" => "§6成功更换了语言设置",
	"lang.noexist" => "§6不存在这种语言",
	"command.not.player" => "你不是玩家",
	"no.permission" => "§c没有权限使用此命令",
	
	"command.mgb" => "小游戏主指令",
	"command.mail" => "邮件系统",
	"command.friend" => "好友系统",
	"command.hide" => "隐藏其他玩家",
	"command.show" => "显示其他玩家",
	"command.lang" => "更换语言",
	
	"not.mgb" => "§c你所在的服务器没有支持§7<§b小游戏核心数据§7>§c,无法使用此功能",
	"not.connected" => "§5小游戏核心数据处理失败,请联系服主",
	"not.login" => "§3你没有登录§7<§b小游戏账号§7>§3,看来得先输入§b/mgb§3来找找办法",
	
	"rename.success" => "§3成功将§7<§b小游戏名称§7>§3更换为§a[§6%0§a]",
	"rename.keyword" => "§a[§6%0§a]§3是一个§c关键词§3,更改§7<§b小游戏名称§7>§3失败!",
	
	"mail.unlogin" => "§3你没有登录§7<§b小游戏账号§7>§3,看来得先输入/mgb来找找办法",
	"mail.send.success" => "§d成功向 §a[%0] §d发送了一封邮件",
	"mail.send.self" => "§c无法向自己发送邮件!",
	"mail.send.noexist" => "§c没有为 §2[%0] §c的账户",
	"mail.read.noexist" => "§c不存在ID为§2[%0]§c的邮件",
	"mail.list.page" => "§3  正在查看第§a[%0]§3页/共§2[%1]§3页邮件",
	"mail.del.success" => "§6已将ID为§a[%0]§6的邮件置入回收站",
	"mail.del.notmine" => "§cID为§2[%0]§c的邮件不是你的,你无法删除它",
	"mail.del.noexist" =>  "§c不存在ID为§2[%0]§c的邮件",
	"mail.delall.success" => "§6已将§a所有§6的邮件置入回收站",
	"mail.del.save7" => "§a回收站中的邮件将只保存§e[7天]",
	"mail.restore.success" => "§6已将ID为§a[%0]§6的邮件恢复",
	"mail.restore.notmine" => "§cID为§2[%0]§c的邮件不是你的,你无法恢复它",
	"mail.restore.noexist" =>  "§c不存在ID为§2[%0]§c的邮件",
	"mail.restoreall.success" => "§6已将§a所有§6的邮件恢复",
	"mail.secret.success" => "§d成功向 §a[%0] §d发送了一封悄悄话，不过对方没法回复哦~",
	"mail.secret.self" => "§c无法向自己发送悄悄话!",
	"mail.secret.noexist" => "§c没有为 §2[%0] §c的账户",
	"mail.bottle.success" => "§3已经将藏有§7[%0]§3小纸条的漂流瓶丢向大海了!",
	"mail.bottle.to" => "§8浪似沾湿我身，却是感触的眼泪，一个§9[漂流瓶]§8漂至到我沾湿的脚边\n §8输入§b[/mail]§8进行操作",
	"mail.check" => "§8嗨,我有新的§b邮件消息§8啦! \n §8我有§a[%0]§8个未读消息 \n §8输入§b[/mail]§8进行操作",
	
	
	"f.unlogin" => "§3我没有登录§7<§b小游戏账号§7>§3,看来得先输入/mgb来找找办法",
	"f.make.success" => "§3我现在已与§b[%0]§3成为好友",
	"f.accept.to" => "§3玩家§b[%0]§3同意添加您为好友",
	"f.refuse.to" => "§3玩家§b[%0]§3拒绝添加您为好友",
	"f.add.to" => "§3玩家§b[%0]§3请求添加您为好友",
	"f.del.to" => "§3玩家§b[%0]§3请求添加您为好友",
	"f.add.self" => "§c无法向自己发送交友请求!",
	"f.add.offline" => "§c您要添加的§b[%0]§c玩家没有上线",
	"f.add.unlogin" => "§c您要添加的§b[%0]§c玩家没有登录§7<§b小游戏账号§7>",
	"f.add.noexist" => "§c不存在账户为§b[%0]§c的玩家",
	"f.add.already" => "§c玩家§b[%0]§c已经是你的好友了",
	"f.add.success" => "§3成功向玩家§b[%0]§3发出好友请求",
	"f.del.noexit"=> "§c不存在账户为§b[%0]§c的好友",
	"f.del.success"=> "§3玩家§b[%0]§3不再是是你的好友",
	"f.list.page" => "§3  正在查看第§e[%0]§3页/共§2[%1]§3页好友列表",
	"f.request.page" => "§3  我正在查看第§e[%0]§3页/共§2[%1]§3页好友请求",
	"f.accept.all.success"=> "§3成功添加§2[全部%0个好友请求]§3为您的好友",
	"f.accept.all.noexist" => "§3您的好友请求列表是空的~",
	"f.accept.noexist" => "§c不存在ID为§e[%0]§c的好友请求",
	"f.accept.notmine" => "§c不存在ID为§e[%0]§c的好友请求",
	"f.accept.success" => "§3我同意了ID为§e[%0]§3的好友请求",
	"f.refuse.all.success" => "§3成功拒绝§2[全部%0个好友请求]",
	"f.refuse.all.noexist" => "§3您的好友请求列表是空的~",
	"f.refuse.noexist" => "§c不存在ID为§e[%0]§c的好友请求",
	"f.refuse.notmine" => "§c不存在ID为§e[%0]§c的好友请求",
	"f.refuse.success" => "你拒绝了ID为§e[%0]§3的好友请求",
	"f.check" => "§7嗨,有新的§3好友请求§7啦! \n §7我有§a[%0]§7个好友请求 \n §7输入§b[/friend]§7进行操作",
	),

	];
	
	public static  $random=[
	"ch" => array(
	"mail.list.rand1" => "有话想对朋友说却不想当面说?赶紧用悄悄话 [secret] 功能吧",
	"mail.list.rand2" => "让漂流瓶 [bottle] 流向远方，等待有缘人从海中提起~",
	"mail.list.rand3" => "消息太多了？可以删除全部哦 [del all],而且可以七天内回收哦",
	"mail.list.rand4" => "禁止随地大小便，违者没收工具！",
	"f.list.rand1" => "岁不寒无以知松柏，事不难无以知君子",
	"f.list.rand2" => "贫贱之交不可忘，糟糠之妻不下堂",
	"f.list.rand3" => "鹿鸣得食而相呼，伐木同声而求友",
	"f.list.rand4" => "听说可以输入/friend add :XXX直接申请添加XXX这个玩家为好友哦（没有冒号的话一般默认XXX为小游戏账号）",
	),
	
	];
}
?>
