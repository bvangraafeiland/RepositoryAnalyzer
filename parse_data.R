library(ggplot2)
library(tools)
library(rjson)

language_distribution <- read.csv("results/basic_data.csv", row.names=1)
repository_data <- read.csv("results/repository_data.csv", row.names=1)
pull_request_stats <- read.csv("results/pull_request_data.csv", row.names=1)
asat_prevalence <- read.csv("results/asat_prevalence.csv", header = FALSE)
warning_counts <- retrieveWarningCounts()
average_warning_counts <- read.csv("results/average_warning_counts.csv", header = TRUE, row.names = 1)
classification_counts <- read.csv("results/warning_classification_counts.csv", header = FALSE)
classification_counts_grouped <- read.csv("results/warning_classification_counts_grouped.csv", header = TRUE, row.names = 1)
solve_times <- retrieveSolveTimes()
solve_time_means <- unlist(lapply(solve_times, function(list) { mean(list$V1)}))
solve_time_medians <- unlist(lapply(solve_times, function(list) { median(list$V1)}))
normalized_solve_times <- retrieveNormalizedSolveTimes()
normalized_solve_time_means <- unlist(lapply(normalized_solve_times, function(list) { mean(list$V1)}))
normalized_solve_time_medians <- unlist(lapply(normalized_solve_times, function(list) { median(list$V1)}))

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
  return(getRepositoryData(usesAsats)[,attribute])
}

getPullRequestAttribute <- function(attribute, usesAsats)
{
  return(getPullRequestData(usesAsats)[,attribute])
}

testRepositoryAge <- function(language)
{
  data <- repository_data[repository_data$language == language,]
  
  wilcox.test(age ~ uses_asats, data = data)
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

analyzeRepositoryAttribute <- function(attribute, alternative = "two.sided")
{
  noAsats <- getRepositoryAttribute(attribute, 0)
  asats <- getRepositoryAttribute(attribute, 1)

  wilcox.test(noAsats, asats, alternative = alternative)
}

analyzePullRequestAttribute <- function(attribute, alternative = "two.sided")
{
  noAsats <- getPullRequestAttribute(attribute, 0)
  asats <- getPullRequestAttribute(attribute, 1)

  wilcox.test(noAsats, asats, alternative = alternative)
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

retrieveSolveTimes <- function()
{
  files <- list.files(path="results/solve_time_per_category_filtered", pattern="*.csv", full.names=TRUE, recursive=TRUE)
  list <- lapply(files, function(x) {
    read.table(x)
  })
  names(list) <- lapply(files, function(x) {
    file_path_sans_ext(basename(x))
  })
  return(list)
}

retrieveNormalizedSolveTimes <- function()
{
  files <- list.files(path="results/solve_times_per_category_normalized", pattern="*.csv", full.names=TRUE, recursive=TRUE)
  list <- lapply(files, function(x) {
    read.table(x)
  })
  names(list) <- lapply(files, function(x) {
    file_path_sans_ext(basename(x))
  })
  return(list)
}

plotWarningCounts <- function()
{
  # setEPS()
  mapply(function(repository, name) {
    png(paste("graphs/", name, ".png", sep = ""))
    # postscript(paste("graphs/", name, ".eps", sep = ""))
    plot(repository$warnings_count, type="l", xlab="Commit", ylab="Warning count", main=name)
    dev.off()
  }, warning_counts, names(warning_counts))
}

plotWarningsPer100 <- function()
{
  plot(average_warning_counts$loc, average_warning_counts$warnings_per_100_loc, xlab = "Total lines of code", ylab = "Warning count per 100 lines of code")
}

plotClassificationCounts <- function()
{
  par(mar = c(12, 4, 4, 2) + 0.1)
  barplot(classification_counts$V2 / 100000, names.arg = classification_counts$V1, las = 2, ylab = "Number of warnings (x100,000)")
  par(mar = c(5, 4, 4, 2) + 0.1)
}

plotClassificationCountsFor <- function(language)
{
  counts <- classification_counts_grouped[language]
  par(mar = c(12, 4, 4, 2) + 0.1)
  barplot(counts[,] / 100000, names.arg = row.names(counts), las = 2, ylab = "Number of warnings (x100,000)")
  par(mar = c(5, 4, 4, 2) + 0.1)
}

plotSolvetimes <- function()
{
  par(mar = c(12, 4, 4, 2) + 0.1)
  barplot(sort(normalized_solve_time_means, decreasing = TRUE), las = 2, ylab = "Average number of commits to solve")
  par(mar = c(5, 4, 4, 2) + 0.1)
}

plotSolvetimeMedians <- function()
{
  par(mar = c(12, 4, 4, 2) + 0.1)
  barplot(tail(sort(normalized_solve_time_medians, decreasing = TRUE), -1), las = 2, ylab = "Median number of commits to solve")
  par(mar = c(5, 4, 4, 2) + 0.1)
}

testWarningsAgainstTravis <- function()
{
  wilcox.test(warnings_per_100_loc ~ asat_in_travis, data=average_warning_counts)
}

getConsecutivePairs <- function(list)
{
  cbind(list[-length(list)], list[-1])
}

testSolveTimesPair <- function(row)
{
  pair <- getConsecutivePairs(names(sort(normalized_solve_time_means, decreasing = TRUE)))[row,]
  first <- get(pair[1], normalized_solve_times)$V1
  second <- get(pair[2], normalized_solve_times)$V1
  wilcox.test(first, second)
}

testAllSolveTimesPairs <- function()
{
  pairs <- getConsecutivePairs(names(sort(normalized_solve_time_means, decreasing = TRUE)))
  numPairs <- length(pairs) / 2
  
  testResults <- lapply(1:numPairs, function(row) {
    testSolveTimesPair(row)
  })
  pValues <- lapply(testResults, function (result) {
    result$p.value
  })
  uValues <- lapply(testResults, function (result) {
    as.numeric(result$statistic)
  })
  setSizes <- lapply(1:numPairs, function(row) {
    firstSize <- length(get(pairs[row,1], normalized_solve_times)$V1)
    secondSize <- length(get(pairs[row,2], normalized_solve_times)$V1)
    paste(firstSize, secondSize, sep = " and ")
  })
  categoryPairs <- lapply(1:numPairs, function(row) {
    paste(pairs[row,1], pairs[row,2], sep = " and ")
  })
  
  result <- data.frame(matrix(c(categoryPairs, pValues, uValues, setSizes), length(testResults)))
  names(result) <- c("Categories", "p-value", "U-value", "Sample sizes")
  return(result)
}