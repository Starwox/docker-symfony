output "aws_zones" {
  value = ["${var.aws_zones}"]
}
output "db_subnet_group_name" {
  value = "${aws_db_subnet_group.db_subnet_group.name}"
}
output "ecs_cluster_name" {
  value = "${aws_ecs_cluster.ecs_cluster.name}"
}
output "ecsServiceRole_arn" {
  value = "${aws_iam_role.ecsServiceRole.arn}"
}

output "vpc_default_sg_id" {
  value = "${data.aws_security_group.vpc_default_sg.id}"
}
output "vpc_id" {
  value = "${aws_vpc.vpc.id}"
}