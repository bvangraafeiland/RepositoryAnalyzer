library(ggplot2)
library(tools)

language_distribution <- read.csv("results/basic_data.csv", row.names=1)
repository_data <- read.csv("results/repository_data.csv", row.names=1)
pull_request_stats <- read.csv("results/pull_request_data.csv", row.names=1)
asat_prevalence <- read.csv("results/asat_prevalence.csv", header = FALSE)
warning_counts <- retrieveWarningCounts()

getRepositoryData <- function(usesAsats)
{
  return(repository_data[repository_data$uses_asats == usesAsats,])
}

getPullRequestData <- function(usesAsats)
{
  return(pull_request_stats[pull_request_stats$uses_asats == usesAsats,])
}

getRepositoryAttribute <- function(attribute, usesAsats)
{
  return(getRepositoryData(usesAsats)[attribute])
}

getPullRequestAttribute <- function(attribute, usesAsats)
{
  return(getPullRequestData(usesAsats)[attribute])
}

repositoryDataSubset <- function(property)
{
  return(repository_data[,c(property, "uses_asats")])
}

pullRequestDataSubset <- function(property)
{
  return(pull_request_stats[,c(property, "uses_asats")])
}

plotPullCounts <- function(asats)
{
  barplot(table(getRepositoryData(asats)$pull_request_count), xlab = "Total amount of pull requests", ylab = "Number of repositories")
}

plotStarCounts <- function(asats)
{
  barplot(table(getRepositoryData(asats)$stargazers_count), xlab = "Amount of stars", ylab = "Number of repositories")
}

plotAsatPrevalence <- function()
{
  barplot(asat_prevalence$V2, names.arg = asat_prevalence$V1, ylab = "Number of projects")
}

makeBoxplot <- function(dataset, attr, ylabel = NULL)
{
  #ggplot(dataset, aes_string("uses_asats", attr, fill="uses_asats")) + geom_boxplot(aes(group=uses_asats)) + theme(axis.text.x=element_blank(), axis.ticks.x=element_blank(), axis.title.x=element_blank())
  boxplot(dataset[,attr] ~ dataset$uses_asats == 1, xlab="Using ASATs", ylab = ylabel)
}

plotRepositoryAge <- function()
{
  makeBoxplot(repository_data, "age", "Repository age (hours)")
}

plotUniqueContributors <- function()
{
  makeBoxplot(pull_request_stats, "unique_user_count", "Number of unique contributors")
}

plotMergedCount <- function()
{
  makeBoxplot(pull_request_stats, "merged_count", "Amount of merged PRs out of last 100")
}

plotTimeToClose <- function()
{
  makeBoxplot(pull_request_stats, "time_to_close", "Average time to close pull requests (seconds)")
}

analyzeRepositoryAttribute <- function(attribute)
{
  noAsats <- getRepositoryAttribute(attribute, 0)
  asats <- getRepositoryAttribute(attribute, 1)

  t.test(noAsats, asats)
}

analyzePullRequestAttribute <- function(attribute)
{
  noAsats <- getPullRequestAttribute(attribute, 0)
  asats <- getPullRequestAttribute(attribute, 1)

  t.test(noAsats, asats)
}

retrieveWarningCounts <- function()
{
  files <- list.files(path="results/warning_counts", pattern="*.csv", full.names=TRUE, recursive=TRUE)
  list <- lapply(files, function(x) {
    read.csv(x, row.names = 1)
  })
  names(list) <- lapply(files, function(x) {
    file_path_sans_ext(basename(x))
  })
  return(list)
}

plotWarningCounts <- function()
{
  setEPS()
  mapply(function(repository, name) {
    # png(paste("graphs/", name, ".png", sep = ""))
    postscript(paste("graphs/", name, ".eps", sep = ""))
    plot(repository$warnings_count, type="l", xlab="Commit", ylab="Warning count", main=name)
    dev.off()
  }, warning_counts, names(warning_counts))
}